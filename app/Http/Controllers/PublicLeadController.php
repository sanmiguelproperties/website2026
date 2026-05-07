<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest;
use App\Services\PublicLeadCaptureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PublicLeadController extends Controller
{
    public function store(Request $request, PublicLeadCaptureService $leadCapture): JsonResponse
    {
        $sourceOptions = [
            ContactRequest::SOURCE_CONTACT_PAGE_FORM,
            ContactRequest::SOURCE_HOME_CONTACT_FORM,
            ContactRequest::SOURCE_FOOTER_NEWSLETTER,
        ];

        $validator = Validator::make($request->all(), [
            'source' => ['required', 'string', Rule::in($sourceOptions)],
            'lead_type' => ['nullable', 'string', Rule::in(array_keys(ContactRequest::leadTypeLabels()))],
            'contact_type' => ['nullable', 'string', Rule::in(array_keys(ContactRequest::contactTypeLabels()))],
            'name' => ['nullable', 'string', 'max:255'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'interest' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:3000'],
            'privacy' => ['nullable'],
            'source_url' => ['nullable', 'string', 'max:2048'],
            'referrer_url' => ['nullable', 'string', 'max:2048'],
            'locale' => ['nullable', 'string', 'max:10'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
        ]);

        $validator->after(function ($validator) use ($request): void {
            $source = (string) $request->input('source');

            if ($source === ContactRequest::SOURCE_FOOTER_NEWSLETTER) {
                return;
            }

            $name = trim((string) ($request->input('full_name') ?: $request->input('name')));
            if ($name === '') {
                $validator->errors()->add('name', 'El nombre es obligatorio.');
            }

            if (blank($request->input('phone'))) {
                $validator->errors()->add('phone', 'El telefono es obligatorio.');
            }

            if (!$request->boolean('privacy')) {
                $validator->errors()->add('privacy', 'Debes aceptar la politica de privacidad.');
            }

            if ($source === ContactRequest::SOURCE_CONTACT_PAGE_FORM && blank($request->input('message'))) {
                $validator->errors()->add('message', 'El mensaje es obligatorio.');
            }
        });

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $lead = $leadCapture->capture($validator->validated(), $request);

        return $this->apiCreated('Solicitud registrada', 'PUBLIC_LEAD_CREATED', [
            'id' => $lead->id,
        ]);
    }
}
