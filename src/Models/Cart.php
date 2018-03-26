<?php

namespace Msonowal\Laracart\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['cart_type', 'email', 'customer_id', 'shipping_cost','discount_code', 'discount_amount', 'discount_type'];

    const CART_TYPE_ABANDONED   =   'abandoned';
    const CART_TYPE_ORDERED     =   'ordered';
    const CART_TYPE_WISHLIST    =   'wishlist';
    const DISCOUNT_TYPE_NONE    =   0;
    const DISCOUNT_TYPE_PERCENTAGE  =   1;
    const DISCOUNT_TYPE_AMOUNT  =   2;

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
    public function scopeValid($query)
    {
        return $query->where('cart_type', self::CART_TYPE_ABANDONED);
    }
    public function scopeByCustomer($query,$customer_id)
    {
        return $query->where('customer_id',$customer_id);
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
    public function removeShippingCost()
    {
        $this->shipping_cost    =   0;
        $this->save();
    }
    public function removeDiscount()
    {
        $this->discount_amount      =   0;
        $this->discount_type        =   self::DISCOUNT_TYPE_NONE;
        $this->discount_code        =   null;
        $this->save();
    }
    public function setDiscount($amount, $type, $code=null)
    {
        $this->discount_amount      =   $amount;
        $this->discount_type        =   $type;
        $this->discount_code        =   $code;
        $this->save();
    }
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }
}
