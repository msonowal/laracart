<?php

namespace Msonowal\Laracart\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['session_key', 'cart_type', 'email', 'customer_id', 'shipping_cost'];

    const CART_TYPE_ABANDONED   =   'abandoned';
    const CART_TYPE_ORDERED     =   'ordered';
    const CART_TYPE_WISHLIST    =   'wishlist';

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
    public function isGuest()
    {
        return ($this->customer_id == null || $this->customer_id=='');
    }
    public function changeType($type=self::CART_TYPE_ORDERED)
    {
        $this->cart_type    =   $type;
        $this->save();
    }
    public function setShippingCost($cost)
    {
        $this->shipping_cost    =   $cost;
        $this->save();
    }
}
