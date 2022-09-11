<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Safaricom\Mpesa\Mpesa;

class MpesaController extends Controller
{
    public function stkPush(Request $request)
    {
        $mpesa = new Mpesa();
        $input = $request->all();
        {
            $number = "254" . trim(substr($input["phone"], -9));
            $LipaNaMpesaPasskey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
            $Amount = $input["amount"];
            $BusinessShortCode = "174379";
            $PartyA = $number;
            $PartyB = $BusinessShortCode;
            $PhoneNumber = $number;
            $CallBackURL = "https://apps.kibeti.co.ke/carparts/api/mpesa/stkpushcallback";
            $AccountReference = $input["accountReference"];
            $TransactionDesc = "Customer Shopping";
            $Remarks = 'OK';
            $TransactionType = 'CustomerPayBillOnline';

            if (self::is_connected()) {
                $stkPushSimulation = $mpesa->STKPushSimulation($BusinessShortCode, $LipaNaMpesaPasskey, $TransactionType, $Amount, $PartyA, $PartyB, $PhoneNumber, $CallBackURL, $AccountReference, $TransactionDesc, $Remarks);
                //dd($stkPushSimulation);
                $res = json_decode($stkPushSimulation, true);
                //  sleep(30);
                if (isset($res['errorCode'])) {
                    $json = array();
                    $json['success'] = false;
                    $json['message'] = $res['errorMessage'];
                    //    \Session::put('error',  $res['errorMessage']);
                    return response()->json($json);
                }
                if (isset($res['ResponseCode']) && $res['ResponseCode'] == 0) {
                    $value = true;
                    $response =  json_decode($stkPushSimulation);
                    $response->success = $value;
                    $response->message = "Success. Payment request sent to your phone";
                    return response()->json($response);
                } else {
                    $json = array();
                    $json['success'] = false;
                    $json['message'] = "Unknown Error";
                    //    \Session::put('error',  $res['errorMessage']);
                    return response()->json($res);
                }
            } else {
                $json = array();
                $json['success'] = false;
                $json['message'] = 'No internet connection';
                // \Session::put('error', 'Connection timeout');
                return response()->json($json, $res);
            }
        }
    }

    public function confirm(Request $request)
    {
        $mpesa = new Mpesa();
        $input = $request->all();

        $LipaNaMpesaPasskey = "bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919";
        $BusinessShortCode = "174379";
        $timestamp = date("YmdHis");
        $password = base64_encode($BusinessShortCode . $LipaNaMpesaPasskey . $timestamp);

        if (self::is_connected()) {
            $STKPushRequestStatus = $mpesa->STKPushQuery('live', $input['checkoutRequestId'], $BusinessShortCode, $password, $timestamp);
            $re = json_decode($STKPushRequestStatus, true);

            if (isset($re['ResultCode']) && $re['ResultCode'] == 0) {
                $json = array();
                $json['success'] = 1;
                $json['message'] = "Payment processed successfully";
                //  \Session::put('success', 'Payment processed successfully');

                return response()->json($re);
            } else {

                return response()->json($re);
            }
        } else {
            $json = array();
            $json['success'] = 0;
            $json['message'] = 'not_connected';
            //   \Session::put('error', 'Connection timeout');
            return response()->json($json);
            // return view("errors.not_connected");
        }
    }
    public function is_connected()
    {
        $connected = @fsockopen("www.example.com", 80);
        //website, port  (try 80 or 443)
        if ($connected) {
            $is_conn = true; //action when connected
            fclose($connected);
        } else {
            $is_conn = false; //action in connection failure
        }
        return $is_conn;
    }
}
