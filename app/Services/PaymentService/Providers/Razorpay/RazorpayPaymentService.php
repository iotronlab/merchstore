<?php

namespace App\Services\PaymentService\Providers\Razorpay;

use App\Services\PaymentService\Contracts\PaymentProviderContract;
use App\Services\PaymentService\Contracts\Provider\PaymentProviderMethodContract;
use App\Services\PaymentService\Contracts\Provider\PaymentProviderPayoutContract;
use App\Services\PaymentService\Contracts\Provider\PaymentProviderRefundContract;
use App\Services\PaymentService\Contracts\Provider\PaymentProviderVerificationContract;
use App\Services\PaymentService\Providers\Razorpay\Actions\ContactAction;
use App\Services\PaymentService\Providers\Razorpay\Actions\FundAccountAction;
use App\Services\PaymentService\Providers\Razorpay\Actions\OrderAction;
use App\Services\PaymentService\Providers\Razorpay\Actions\PayoutAction;
use App\Services\PaymentService\Providers\Razorpay\Actions\RefundAction;
use App\Services\PaymentService\Providers\Razorpay\Actions\VerifyAction;




class RazorpayPaymentService implements PaymentProviderContract,RazorpayPaymentServiceContract
{

    private RazorpayApi $api;
    private ?string $error = null;
    protected string $speed = 'normal';
    private RazorpayApi $apiX;

    public function __construct(RazorpayApi $api)
    {
        $this->api = $api;
        $this->discoverConfig();
    }

    protected function discoverConfig()
    {
        $this->speed = config('payment-provider.providers.razorpay.speed');
        $this->apiX = $this->getRazorpayXApi();
    }

    public function getSpeed(): string
    {
        return $this->speed;
    }



    /**
     * @return PaymentProviderMethodContract
     */
    public function order(): PaymentProviderMethodContract
    {
        return new OrderAction($this->api,$this);
    }

    /**
     * @return PaymentProviderMethodContract
     */
    public function payment(): PaymentProviderMethodContract
    {
        // TODO: Implement payment() method.
    }

    /**
     * @return PaymentProviderVerificationContract
     */
    public function verify(): PaymentProviderVerificationContract
    {
        return new VerifyAction($this->api,$this);
    }

    /**
     * @return PaymentProviderRefundContract
     */
    public function refund(): PaymentProviderRefundContract
    {
        return new RefundAction($this->api,$this);
    }


    /**
     * @return PaymentProviderPayoutContract
     */
    public function payout(): PaymentProviderPayoutContract
    {
        return new PayoutAction($this->apiX,$this);
    }

    public function contact()
    {
        return new ContactAction($this->apiX,$this);
    }

    public function fundAccount()
    {
        return new FundAccountAction($this->apiX,$this);
    }



    /**
     * @return object
     */
    public function getApi(): object
    {
        return $this->api;
    }

    private function getRazorpayXApi(): RazorpayApi
    {
        return new RazorpayApi(config('services.razorpay.api_x_key'), config('services.razorpay.api_x_secret'));
    }

    /**
     * @return string
     */
    public function getProviderName(): string
    {
        return 'razorpay';
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return get_class($this);
    }


    /**
     * @param string $error
     * @return void
     */
    public function setError(string $error): void
    {
        $this->error = $error;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }


    /**
     * @return string
     */
    public function getWebhookSecret(): string
    {
        return config('services.razorpay.webhook_secret');
    }


    public function getCompanyBankAccount():string
    {
        return config('services.razorpay.payout.account_no');
    }

    public function payoutMode():string
    {
        return config('services.razorpay.payout.mode');
    }



}
