<?php

namespace App\Services\OrderService\Return;

use App\Helpers\Money\Money;
use App\Models\Order\Order;
use App\Models\Order\OrderProduct;
use App\Models\Order\OrderShipment;
use App\Models\Payment\Payment;
use App\Models\Payment\Refund;
use App\Services\PaymentService\Contracts\PaymentProviderContract;
use App\Services\PaymentService\PaymentService;
use App\Services\ShippingService\ShippingService;
use Illuminate\Support\Collection;

class OrderReturnRefundService
{

    protected PaymentService $paymentService;
    protected ShippingService $shippingService;
    protected Order $order;
    protected ?Payment $payment;
    protected ?string $error = null;

    private ?string $sku = null;
    protected ?OrderProduct $specificOrderProduct = null;
    protected ?Collection $returnableOrderProducts = null;
    protected PaymentProviderContract $paymentProvider;

    protected array $returnPlacedBag = [];



    public function __construct(Order $order, PaymentService $paymentService, ShippingService $shippingService,?string $given_sku=null)
    {
        $this->order = $order;

        $this->order->loadMissing([
            'refunds',
            'payment',
            'payment.provider',
            'orderProducts',
            'orderProducts.refund',
            'orderProducts.product',
            'orderProducts.shipment',
            'orderProducts.shipment.shippingProvider',
            'orderProducts.shipment.pickupAddress',
            'orderProducts.shipment.deliveryAddress',
        ]);

        // Is there Any Payment Available For Return The Order
        if (is_null($this->order->payment->provider_ref_id))
        {
            $this->error = 'order : '.$this->order->uuid.' not paid yet!';
        }

        $this->payment = $this->order->payment;

        if (!$this->payment->verified)
        {
            $this->error = 'Payment : '.$this->payment->receipt.' not verified!';
        }


        $this->sku = $given_sku;
        $this->shippingService = $shippingService;
        $this->paymentService = $paymentService;

        if (is_null($this->payment) && is_null($this->payment->provider_ref_id))
        {
            $this->error = 'Order has not paid yet!';
        }else{
            $this->paymentProvider = $this->paymentService->provider($this->payment->provider->code)->getProvider();

            $this->specificOrderProduct = $this->order->orderProducts->first(function ($orderProduct) {
                return $orderProduct->product->sku === $this->sku;
            });

            $this->returnableOrderProducts = $this->order->orderProducts->map(function ($orderProduct) {
                    if ($orderProduct->product->is_returnable) {
                        // Check Return Window Here
                        return $orderProduct;
                    }
                })
                ->filter();
        }

    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function return():bool
    {

        if (is_null($this->error))
        {
            if (!is_null($this->specificOrderProduct))
            {

                // Make Sure Given Sku Product Exist In Returnable Order Products
                if (!$this->returnableOrderProducts->where('id','=',$this->specificOrderProduct->id)->count())
                {
                    $this->error = 'Product : '.$this->specificOrderProduct->product->name.' not returnable!';
                }

                if (!is_null($this->specificOrderProduct->refund) && $this->specificOrderProduct->refund->count())
                {
                    $this->error = 'Product : '.$this->specificOrderProduct->product->name.' already in process for return and refund!';
                }

                if (is_null($this->error))
                {
                    $this->returnThisOrderProduct($this->specificOrderProduct);
                }

            }else{



                foreach ($this->returnableOrderProducts as $orderProduct)
                {

                    if (!is_null($orderProduct->refund) && $orderProduct->refund->count())
                    {
                        $this->error = 'Product : '.$orderProduct->product->name.' already in process for return and refund!';
                    }else{

                        $this->returnThisOrderProduct($orderProduct);
                    }

                }

            }

        }


        if (is_null($this->error))
        {
            $this->makePendingRefund();
        }


        return is_null($this->error);
    }





    protected function returnThisOrderProduct(OrderProduct $orderProduct): void
    {

        foreach ($orderProduct->shipment as $orderShipment)
        {
            // Check The Shipment Has Provider Data
            if (is_null($orderShipment->shipment_id) || is_null($orderShipment->provider_order_id))
            {
                $this->error = 'Product sku: '.$orderProduct->product->sku.' not shipped yet!';
            }

            // Check For Error
            if (is_null($this->error))
            {

                // Everything Is Fine For Return This OrderProduct With Whole Quantity
                $response = $this->shippingService->provider($orderShipment->shippingProvider->code)
                    ->return()
                    ->create($orderProduct,$orderShipment);

                if (isset($response['order_id']))
                {
                    $orderShipment->fill([
                        'status'            => OrderShipment::RETURNING,
                        'return_order_id'   => $response['order_id'],
                        'return_shipment_id'   => $response['shipment_id'],
                        'details' => array_merge($orderShipment->details,[
                            'return_details' => is_array($response) ? $response : $response->toArray()
                        ])
                    ])->save();


                }else{
                    $this->error = 'Product sku: '.$orderProduct->product->sku.' return process failed!';
                }
            }

        }

        if (is_null($this->error))
        {
            $this->returnPlacedBag [] = $orderProduct;
        }

    }


    protected function makePendingRefund()
    {

        foreach ($this->returnPlacedBag as $orderProduct)
        {


            // Update Payment
            $this->payment->fill([
                'status' => Payment::REFUND
            ])->save();
            // Create And Return Refund Model
            return $orderProduct->refund()->create([
                'amount' => $orderProduct->total,
                'currency' => $orderProduct->total->currency(),
                'payment_id' => $this->payment->id,
                'receipt' => $this->order->payment->receipt,
                'status' => Refund::PENDING,
                'order_id' => $this->order->id,
            ]);





        }



    }







}
