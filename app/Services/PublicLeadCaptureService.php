<?php

namespace App\Services;

use App\Models\ContactRequest;
use App\Models\Property;
use App\Notifications\LeadRoutedNotification;
use App\Support\RbacNotifications;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PublicLeadCaptureService
{
    public function capture(array $payload, Request $request): ContactRequest
    {
        $source = $this->normalizeSource($payload['source'] ?? ContactRequest::SOURCE_CONTACT_PAGE_FORM);
        $interest = $this->normalizeInterest($payload['interest'] ?? null);
        $property = $this->resolveProperty($payload);
        $leadType = $this->normalizeLeadType($payload['lead_type'] ?? null, $interest, $source, $property);
        $contactType = $this->normalizeContactType($payload['contact_type'] ?? null, $leadType, $interest, $source);
        $propertyContext = $this->normalizePropertyContext($payload['property_context'] ?? null, $source, $leadType, $property);
        $tracking = $this->trackingPayload($payload, $request);
        $privacyAcceptedAt = $this->acceptedPrivacy($payload['privacy'] ?? null) ? now() : null;

        $ownerId = $property?->agent_user_id ?: null;
        $propertyPublicId = $this->propertyPublicId($property, $source, $payload);

        $lead = ContactRequest::create([
            'agency_id' => $property?->agency_id,
            'property_id' => $property?->id,
            'owner_id' => $ownerId,
            'property_public_id' => $propertyPublicId,
            'property_address' => $payload['property_address'] ?? null,
            'remote_id' => $this->remoteId($source),
            'source' => $source,
            'source_url' => $tracking['source_url'],
            'referrer_url' => $tracking['referrer_url'],
            'lead_type' => $leadType,
            'contact_type' => $contactType,
            'property_context' => $propertyContext,
            'interest' => $interest,
            'name' => $this->nameFromPayload($payload),
            'email' => $payload['email'] ?? null,
            'phone' => $payload['phone'] ?? null,
            'locale' => $this->normalizeLocale($payload['locale'] ?? $request->input('locale') ?? app()->getLocale()),
            'message' => $this->messageFromPayload($payload, $source),
            'happened_at' => now(),
            'privacy_accepted_at' => $privacyAcceptedAt,
            'status' => 'new',
            'assignment_status' => $ownerId ? 'assigned' : 'pending_assignment',
            'assigned_at' => $ownerId ? now() : null,
            'utm_source' => $tracking['utm_source'],
            'utm_medium' => $tracking['utm_medium'],
            'utm_campaign' => $tracking['utm_campaign'],
            'utm_term' => $tracking['utm_term'],
            'utm_content' => $tracking['utm_content'],
            'raw_payload' => $this->rawPayload($payload, $request, $tracking, $property),
        ]);

        $lead->load(['property', 'agency', 'owner']);
        $this->notifyLeadRouting($lead);

        return $lead;
    }

    private function resolveProperty(array $payload): ?Property
    {
        if (empty($payload['property_id']) || !is_numeric($payload['property_id'])) {
            return null;
        }

        return Property::query()
            ->with(['agency', 'operations.currency'])
            ->find((int) $payload['property_id']);
    }

    private function normalizeSource(string $source): string
    {
        $source = trim($source);

        return $source !== '' ? Str::limit($source, 100, '') : ContactRequest::SOURCE_CONTACT_PAGE_FORM;
    }

    private function normalizeLeadType(?string $leadType, ?string $interest, string $source, ?Property $property): string
    {
        $leadType = strtolower(trim((string) $leadType));

        if (in_array($leadType, array_keys(ContactRequest::leadTypeLabels()), true)) {
            return $leadType;
        }

        return match ($interest) {
            'buy' => ContactRequest::LEAD_TYPE_BUYER,
            'rent' => ContactRequest::LEAD_TYPE_RENTER,
            'sell' => ContactRequest::LEAD_TYPE_SELLER,
            'investment' => ContactRequest::LEAD_TYPE_INVESTOR,
            default => $this->inferLeadTypeFromSource($source, $property),
        };
    }

    private function inferLeadTypeFromSource(string $source, ?Property $property): string
    {
        if ($source === ContactRequest::SOURCE_SELLER_FORM) {
            return ContactRequest::LEAD_TYPE_SELLER;
        }

        if ($source === ContactRequest::SOURCE_FOOTER_NEWSLETTER) {
            return ContactRequest::LEAD_TYPE_NEWSLETTER;
        }

        if ($property) {
            if ((bool) $property->for_rent) {
                return ContactRequest::LEAD_TYPE_RENTER;
            }

            foreach ($property->operations as $operation) {
                $operationType = strtolower((string) $operation->operation_type);
                if (str_contains($operationType, 'rent') || str_contains($operationType, 'renta')) {
                    return ContactRequest::LEAD_TYPE_RENTER;
                }
            }

            return ContactRequest::LEAD_TYPE_BUYER;
        }

        return ContactRequest::LEAD_TYPE_GENERAL;
    }

    private function normalizePropertyContext(?string $propertyContext, string $source, string $leadType, ?Property $property): string
    {
        $propertyContext = strtolower(trim((string) $propertyContext));

        if (in_array($propertyContext, array_keys(ContactRequest::propertyContextLabels()), true)) {
            return $propertyContext;
        }

        if ($property) {
            return ContactRequest::PROPERTY_CONTEXT_EXISTING_LISTING;
        }

        if ($source === ContactRequest::SOURCE_SELLER_FORM || $leadType === ContactRequest::LEAD_TYPE_SELLER) {
            return ContactRequest::PROPERTY_CONTEXT_SELLER_PROPERTY;
        }

        return ContactRequest::PROPERTY_CONTEXT_NONE;
    }

    private function normalizeContactType(?string $contactType, string $leadType, ?string $interest, string $source): ?string
    {
        $value = strtolower(trim((string) $contactType));

        $mapped = match ($value) {
            'buyer', 'comprador', 'buy' => ContactRequest::CONTACT_TYPE_BUYER,
            'seller', 'vendedor', 'sell' => ContactRequest::CONTACT_TYPE_SELLER,
            'buyer_seller', 'both', 'ambos', 'comprador_vendedor', 'buy_sell' => ContactRequest::CONTACT_TYPE_BUYER_SELLER,
            default => null,
        };

        if ($mapped !== null) {
            return $mapped;
        }

        if ($source === ContactRequest::SOURCE_SELLER_FORM || $leadType === ContactRequest::LEAD_TYPE_SELLER || $interest === 'sell') {
            return ContactRequest::CONTACT_TYPE_SELLER;
        }

        if ($source === ContactRequest::SOURCE_FOOTER_NEWSLETTER) {
            return null;
        }

        return ContactRequest::CONTACT_TYPE_BUYER;
    }

    private function normalizeInterest(?string $interest): ?string
    {
        $value = strtolower(trim((string) $interest));

        return match ($value) {
            'buy', 'buyer', 'comprar', 'compra' => 'buy',
            'rent', 'renter', 'rentar', 'renta', 'alquilar' => 'rent',
            'sell', 'seller', 'vender', 'venta' => 'sell',
            'buyer_seller', 'both', 'ambos', 'comprador_vendedor', 'buy_sell' => 'buyer_seller',
            'investment', 'investor', 'inversion', 'invertir' => 'investment',
            'other', 'otro' => 'other',
            default => $value !== '' ? Str::limit($value, 100, '') : null,
        };
    }

    private function propertyPublicId(?Property $property, string $source, array $payload): string
    {
        if ($property) {
            return $property->easybroker_public_id
                ?: $property->mls_public_id
                ?: (string) $property->id;
        }

        if (!empty($payload['property_public_id'])) {
            return Str::limit((string) $payload['property_public_id'], 50, '');
        }

        return match ($source) {
            ContactRequest::SOURCE_SELLER_FORM => 'seller-lead',
            ContactRequest::SOURCE_FOOTER_NEWSLETTER => 'newsletter',
            default => 'general-lead',
        };
    }

    private function remoteId(string $source): string
    {
        return Str::limit(Str::slug($source) . '-' . (string) Str::uuid(), 100, '');
    }

    private function nameFromPayload(array $payload): ?string
    {
        $name = trim((string) ($payload['full_name'] ?? $payload['name'] ?? ''));

        return $name !== '' ? $name : null;
    }

    private function messageFromPayload(array $payload, string $source): string
    {
        $message = trim((string) ($payload['message'] ?? ''));

        if ($message !== '') {
            return $message;
        }

        return match ($source) {
            ContactRequest::SOURCE_PROPERTY_FORM,
            ContactRequest::SOURCE_PROPERTY_DETAIL_FORM => 'Solicitud generada desde el formulario publico de propiedad.',
            ContactRequest::SOURCE_SELLER_FORM => 'Solicitud generada desde la pagina Vende con nosotros.',
            ContactRequest::SOURCE_FOOTER_NEWSLETTER => 'Suscripcion generada desde el newsletter del sitio.',
            default => 'Solicitud generada desde un formulario publico del sitio.',
        };
    }

    private function acceptedPrivacy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'on', 'accepted', 'aceptado'], true);
    }

    private function normalizeLocale(?string $locale): ?string
    {
        $locale = strtolower(trim((string) $locale));

        return in_array($locale, ['es', 'en'], true) ? $locale : null;
    }

    private function trackingPayload(array $payload, Request $request): array
    {
        $sourceUrl = trim((string) ($payload['source_url'] ?? $request->headers->get('referer', '')));
        $referrerUrl = trim((string) ($payload['referrer_url'] ?? ''));
        $urlUtm = $this->utmFromUrl($sourceUrl);

        $tracking = [
            'source_url' => $sourceUrl !== '' ? $sourceUrl : null,
            'referrer_url' => $referrerUrl !== '' ? $referrerUrl : null,
        ];

        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as $key) {
            $value = $payload[$key] ?? $request->query($key) ?? $urlUtm[$key] ?? '';
            if (is_array($value)) {
                $value = reset($value) ?: '';
            }
            $value = trim((string) $value);
            $tracking[$key] = $value !== '' ? Str::limit($value, 255, '') : null;
        }

        return $tracking;
    }

    private function utmFromUrl(?string $url): array
    {
        if (!$url) {
            return [];
        }

        $query = parse_url($url, PHP_URL_QUERY);
        if (!$query) {
            return [];
        }

        parse_str($query, $params);

        return Arr::only($params, ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content']);
    }

    private function rawPayload(array $payload, Request $request, array $tracking, ?Property $property): array
    {
        return [
            'form' => Arr::except($payload, ['privacy']),
            'property_id' => $property?->id ?? $payload['property_id'] ?? null,
            'property_name' => $payload['property_name'] ?? $property?->title,
            'submitted_from' => $tracking['source_url'],
            'referrer_url' => $tracking['referrer_url'],
            'tracking' => Arr::only($tracking, ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content']),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];
    }

    private function notifyLeadRouting(ContactRequest $lead): void
    {
        if ($lead->assignment_status === 'assigned') {
            app(CrmNotificationService::class)->leadCreated($lead);
        }

        if ($lead->assignment_status === 'assigned') {
            RbacNotifications::notifyUsers(
                $lead->owner ? [$lead->owner] : [],
                new LeadRoutedNotification($lead, 'assigned')
            );

            return;
        }

        RbacNotifications::notifyRoles(
            ['super-admin'],
            new LeadRoutedNotification($lead, 'pending_assignment')
        );
    }
}
