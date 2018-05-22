# LARAVEL MPESA API (DARAJA)

This is a wrapper implementation from this package https://github.com/Kabangi/mpesa

## Installation
1) In order to install mpesalaravel, just add the following to your composer.json. Then run `composer update`:

```json
"kabangi/mpesa-laravel": "^1.0.4",
```

2) Open your `config/app.php` and add the following to the `providers` array:

```php
Kabangi\MpesaLaravel\MpesaServiceProvider::class,
```

3) In the same `config/app.php` and add the following to the `aliases ` array: 

```php
'MPESA'   => Kabangi\MpesaLaravel\Facades\Mpesa::class,
```
4) Run the command below to publish the package config file `config/mpesa.php`:

```shell
php artisan vendor:publish
```

## Usage
```php
<?php

namespace YOURNAMESPACE;

use Illuminate\Http\Request;
use MPESA;
use Checkout;
use Invoice;

class CheckoutController extends Controller {
   

    /**
     * @param Request $request
     * @return mixed
     */
    public function checkout(Request $request){
        $rules = [
            'invoice_id' => 'required',
        ];

        $validator = Validator::make($request->all(),$rules);
        if ($validator->fails()) {
            return $validator->errors();
        }
  
        try {
            if(!$request->has('invoice_id')){
                // If no invoice create invoice
                $data = [];
                $invoice = $this->createInvoice($data);
            }else{
                $invoice = Invoice::findOrFail($request->input('invoice_id'));
            }

            if((bool) $invoice->paid === true){
                abort(403,'You have already made a payment for this invoice. Thank you!'); 
            }
            // Prevent double checkout.
            $previousCheckoutAttempt = Checkout::where('invoice_id',$invoice->id)
            ->where('user_id',$request->input('user_id'))
            ->where('status','INPROGRESS')
            ->orWhere(function ($query) use ($invoice) {
                $query->where('status','PROCESSED')
                ->where('user_id',$request->input('user_id'))
                ->where('invoice_id',$invoice->id);
            })
            ->orderBy('created_at', 'desc')
            ->first();

          if($previousCheckoutAttempt){
            try{
                $response = $this->fetchCheckoutStatus($previousCheckoutAttempt->request_id);
                $payment = Payment::where('checkout_id',$previousCheckoutAttempt->id)->first();
                if($response->status == 'INPROGRESS' || $response->status == 'PROCESSED'){
                    // TODO: if no payment is reflected on the DB should invoke the processing of that payment.
                    // since it means there is a delay to us receiving the callback.
                    $errorMsg = $response->status == 'INPROGRESS' ? 'You have a checkout request in progress' : 
                    'You have already made a payment for this invoice. Thank you!';
                    abort(403,$errorMsg); 
                }
            }catch(\Exception $e){
                abort(403,$e->getMessage());
            }
          }
          $mpResponse = MPESA::STKPush([
            'amount' => intval($request->input('amount')),
            'phoneNumber' => str_replace('+','',$phone),
            'accountReference' => '12', 
            'transactionDesc' => "Test Transaction"
          ]);

          // TODO: Handle both errors and response errors from our end
          if(!empty($mpResponse->CheckoutRequestID)){
            $checkout = null;
            // save to db.
            $data = [
                'request_id' => $mpResponse->CheckoutRequestID,
                'invoice_id' => $invoice->id,
                'status' => "INPROGRESS",
                'user_id' => $request->input('user_id')
            ];
            $checkout = Checkout::create($data);
            $response = $checkout;
          }else{
            return abort(503,"Something went wrong. Please try again.");
          }
          return $response;
        }catch(\Exception $e){
            throw $e;
        }
    }
    
     private function fetchCheckoutStatus($requestId){
        try{
            $checkout = Checkout::where('request_id',$requestId)->firstOrFail();
            if($checkout->status !== 'INPROGRESS'){
                return $checkout;
            }
            $mpRes = MPESA::STKStatus([
                'checkoutRequestID' => $requestId
            ]);
            Slack::post(json_encode($mpRes));
            if(!isset($mpRes->ResultCode)){
               // TODO: Figure out what to do. Meanwhile just wait until the 
                // server responds
                // if(!empty($mpRes->errorCode)){
                //     $checkout->status = 'CANCELLED';
                //     $checkout->save();
                // }
                return $checkout;
            }else{
                // Update the local persisted checkouts
                $resultCode = intval($mpRes->ResultCode);
                switch($resultCode){
                    case 0:
                        $checkout->status = 'PROCESSED';
                        $this->processMPESAPayment([],$checkout);
                        break;
                    case 1001:
                        $checkout->status = 'FAULTY';
                        break;
                    case 1037: 
                        $checkout->status = 'TIMEDOUT';
                        break;
                    case 1036:
                        $checkout->status = 'NOTSUPPORTED';
                        break;
                    case 1032:
                        $checkout->status = 'CANCELLED';
                        break;
                    default:
                        $checkout->status = 'NOTSUPPORTED';
                        $mpRes->{'cMessage'} = "Please handle this case";
                        Slack::post(json_encode($mpRes));
                    // Do nothing

                }
                $checkout->save();
                return $checkout;
            }

        }catch(\Exception $e){
            abort(403,$e->getMessage());
        }
    }
   
}
```
