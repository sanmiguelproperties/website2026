<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest;
use App\Services\PublicLeadCaptureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SellerLeadController extends Controller
{
    public function store(Request $request, PublicLeadCaptureService $leadCapture): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'property_address' => ['nullable', 'string', 'max:255'],
            'timeframe' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:3000'],
            'privacy' => ['accepted'],
            'contact_type' => ['nullable', 'string', 'in:' . implode(',', array_keys(ContactRequest::contactTypeLabels()))],
            'source_url' => ['nullable', 'string', 'max:2048'],
            'referrer_url' => ['nullable', 'string', 'max:2048'],
            'locale' => ['nullable', 'string', 'max:10'],
            'utm_source' => ['nullable', 'string', 'max:255'],
            'utm_medium' => ['nullable', 'string', 'max:255'],
            'utm_campaign' => ['nullable', 'string', 'max:255'],
            'utm_term' => ['nullable', 'string', 'max:255'],
            'utm_content' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->apiValidationError($validator->errors()->toArray());
        }

        $data = $validator->validated();
        $messageParts = [
            'Solicitud generada desde la pagina Vende con nosotros.',
        ];

        if (!empty($data['property_address'])) {
            $messageParts[] = 'Direccion de propiedad: ' . $data['property_address'];
        }

        if (!empty($data['timeframe'])) {
            $messageParts[] = 'Tiempo estimado para vender: ' . $data['timeframe'];
        }

        if (!empty($data['message'])) {
            $messageParts[] = 'Mensaje: ' . $data['message'];
        }

        $lead = $leadCapture->capture(array_merge($data, [
            'source' => ContactRequest::SOURCE_SELLER_FORM,
            'lead_type' => ContactRequest::LEAD_TYPE_SELLER,
            'contact_type' => ContactRequest::CONTACT_TYPE_SELLER,
            'property_context' => ContactRequest::PROPERTY_CONTEXT_SELLER_PROPERTY,
            'property_public_id' => 'seller-lead',
            'message' => implode("\n", $messageParts),
        ]), $request);

        return $this->apiCreated('Solicitud registrada', 'SELLER_LEAD_CREATED', [
            'id' => $lead->id,
        ]);
    }
}
