<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    public function createPaymentIntent(Request $request)
    {

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'amount' => 'required|integer', // Add validation rules as needed
            // Add more validation rules for other parameters if necessary
        ]);

        // Check for validation errors
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 400);
        }
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        $paymentIntent = PaymentIntent::create([
            'amount' => $request->input('amount'),
            'currency' => 'eur',
            'payment_method' => $request->input('payment_method'),
            'billing_details' => [
                'name' => $request->input('billing_info.name'),
                'email' => $request->input('billing_info.email'),
                'address' => [
                    'city' => $request->input('billing_info.city'),
                    'country' => $request->input('billing_info.country'),
                    'postal_code' => $request->input('billing_info.postcal_code'),
                ],
                'phone' => $request->input('billing_info.phone'),
            ],
        ]);

        // Send email
        Mail::to($request->user()->email)->send(new OrderConfirmation());

        return response()->json([
            'client_secret' => $paymentIntent->client_secret,
            'message' => 'Paiement réussi'
        ]);
    }
}
