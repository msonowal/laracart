<?php

namespace Msonowal\Laracart\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'product_id', 'quantity', 'price', 'tax_rate', 'variant','options'];

    protected $casts    = [
        'options' => 'array',
        'variant'   =>  'array',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
    public function product()
    {
        return $this->belongsTo(config('laracart.product_model'))->withTrashed();
    }
    public function unitPrice($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        return $this->numberFormat($this->price, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function price($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        $price  =   $this->price   * $this->quantity;
        return $this->numberFormat($price, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function tax($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        $tax    =   $this->price * ($this->tax_rate / 100);
        return $this->numberFormat($tax, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function totalTax($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        $tax    =   $this->tax($decimals, $decimalPoint, $thousandSeperator) * $this->quantity;
        return $this->numberFormat($tax, $decimals, $decimalPoint, $thousandSeperator);
    }
//    public function priceWithTax($decimals = null, $decimalPoint = null, $thousandSeperator = null)
//    {
//        $price  =   $this->price($decimals, $decimalPoint, $thousandSeperator) + $this->totalTax($decimals, $decimalPoint, $thousandSeperator);
//        return $this->numberFormat($price, $decimals, $decimalPoint, $thousandSeperator);
//    }
    public function total($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        $price  =   $this->price($decimals, $decimalPoint, $thousandSeperator) + $this->totalTax($decimals, $decimalPoint, $thousandSeperator);
        return $this->numberFormat($price, $decimals, $decimalPoint, $thousandSeperator);
    }
    private function numberFormat($value, $decimals, $decimalPoint, $thousandSeperator)
    {
        if(is_null($decimals)){
            $decimals = is_null(config('laracart.format.decimals')) ? 2 : config('laracart.format.decimals');
        }
        if(is_null($decimalPoint)){
            $decimalPoint = is_null(config('laracart.format.decimal_point')) ? '.' : config('laracart.format.decimal_point');
        }
        if(is_null($thousandSeperator)){
            $thousandSeperator = is_null(config('laracart.format.thousand_seperator')) ? ',' : config('laracart.format.thousand_seperator');
        }
        if(config('laracart.allow_number_format')){
            return number_format($value, $decimals, $decimalPoint, $thousandSeperator);
        }
        return $value;
    }
}
