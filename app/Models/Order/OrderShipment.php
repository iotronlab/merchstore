<?php

namespace App\Models\Order;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Localization\Address;
use App\Models\Shipping\ShippingProvider;

class OrderShipment extends Model
{
    use HasFactory;

    // shipment status
    public const PROCESSING = 'processing';
    public const REVIEW = 'review';
    public const PACKING = 'packing';
    public const READYTOSHIP = 'readytoship';
    public const INTRANSIT = 'intransit';
    public const DELIVERED = 'delivered';
    public const CANCELLED = 'cancelled';

    public const StatusOptions = [
        self::PROCESSING => 'Processing',
        self::REVIEW => 'Review',
        self::PACKING => 'Packing',
        self::READYTOSHIP => 'Ready To Ship',
        self::INTRANSIT => 'In Transit',
        self::DELIVERED => 'Delivered',
        self::CANCELLED => 'Cancelled'
    ];

    protected $fillable = [
        'invoice_uid',
        'total_quantity',
        'pickup_address',
        'delivery_address',
        'tracking_id',
        'shipping_provider_id',
        'last_update',
        'cod',
        'status',
    ];



    public function pickupAddress()
    {
        return $this->belongsTo(Address::class, 'pickup_address');
    }

    public function deliveryAddress()
    {
        return $this->belongsTo(Address::class, 'delivery_address');
    }

    public function orderProducts()
    {
        return $this->belongsToMany(OrderProduct::class, 'product_shipments')->withPivot('product_quantity');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingProvider::class);
    }



}
