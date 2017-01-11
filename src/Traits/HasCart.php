<?php
namespace Msonowal\Laracart\Traits;

use Msonowal\Laracart\Models\Cart;

trait HasCart
{
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
}