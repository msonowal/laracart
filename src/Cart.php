<?php
namespace Msonowal\Laracart;

use Closure;
use Cookie;
//use Illuminate\Support\Collection;
use Msonowal\Laracart\Models\Cart as CartModel;
//use Crypt;
use Msonowal\Laracart\Exceptions\InvalidItemException;

class Cart
{
    const DEFAULT_CART_TYPE =   'abandoned'; // recovered / wishlist');
    public $instance;

    public function __construct($id=null)
    {
        if ($id!=null) {
            $this->instance     =   $this->loadCartById($id);
        }else{
            $this->instance     =   $this->getOrCreate();
        }
    }
    public function getCookieName()
    {
        return config('laracart.cookie_name', '_laracart');
    }
    public function getCookieValue($key=null)
    {
        if (is_null($key)) {
            $key    =  $this->getCookieName();
        }
        if( !Cookie::has($key) ) {
            return null;
        }
        //dd('', Cookie::get($key));
//        if (config('session.encrypt', false) ) {
//            return Crypt::decrypt(Cookie::get($key));
//        }
        return Cookie::get($key);
    }
    protected function getOrCreate()
    {
        $cartId = $this->getCookieValue();
        if ($cartId) return $this->getCartByCookie($cartId);
        return $this->createCart();
    }
    protected function getCartByCookie($id)
    {
        $cart = CartModel::find($id);
        if (!is_null($cart)) {
            //if ($cart) return $cart;
            //$cart->load('items');
            return $cart;
        }
        return $this->createCart();
    }
    protected function createCart($cart_type=self::DEFAULT_CART_TYPE)
    {
        $cart   =   CartModel::create(['cart_type'=> $cart_type]);
        Cookie::queue(Cookie::make($this->getCookieName(), $cart->id, config('laracart.lifetime')));
        return $cart;
    }
    public function destroyCurrentCart($converted=true)
    {
        if ($converted) {
            $this->setType(CartModel::CART_TYPE_ORDERED);
        }
        Cookie::queue(Cookie::forget($this->getCookieName()));
    }
    public function setType($cart_type=self::DEFAULT_CART_TYPE)
    {
        $this->instance->changeType($cart_type);
    }
    protected function loadCartById($id)
    {
        $cart   =   CartModel::find($id);
        if (!is_null($cart)) {
            $cart->load('items');
            return $cart;
        }
        return $cart;
    }
    public function getInstance()
    {
        return $this->instance;
    }
    public function setCustomer($id)
    {
        if ($this->instance->isGuest()) {
            $this->instance->update(['customer_id' => $id]);
        }else{
            //TODO if he has logged in as other customer then destroy or re-create the cart
        }
    }
    public function setEmail($email)
    {
        $this->instance->update(['email' => $email]);
    }
    public function isItemAlreadyExists($id, $variant, $options=[])
    {
        return $this->instance->items->contains(function ($item) use ($id, $variant, $options) {
                    return data_get($item, 'product_id') == $id && data_get($item, 'variant') == $variant && data_get($item, 'options')==$options;
                });
    }
    public function add($id, $variant = [], $qty=1, $price = null, array $options = [])
    {
        $this->instance->load('items');

        if ($this->isItemAlreadyExists($id, $variant, $options)) {

            $cart_item  =   $this->incrementQuantity($id, $qty, $variant, $options);

        }else{
            $tax_rate   =   config('laracart.tax_rate', 0.00);
            $cart_item  =   $this->createCartItem($id, $variant, $qty, $price, $tax_rate, $options);
        }
        $this->refresh();
        return $cart_item;
    }
    public function incrementQuantity($id, $qty, $variant, $options)
    {
        //$cart_item  =   $this->instance->items->where('product_id', $id);
        $cart_item  =   $this->instance->items->where('product_id', $id)->where('variant', $variant)->where('options', $options);
//        if (!is_null($variant)) {
//            $cart_item  =   $cart_item->where('variant', $variant);
//        }
        $cart_item  =   $cart_item->first();
        if (is_null($cart_item)) {
            throw new InvalidItemException("This cart does not contain line item {$id}. or does not match the criteria");
        }
        $cart_item->quantity += $qty; //TODO implement zero and remove
        $cart_item->save();
        return $cart_item;
    }
    public function updateQuantity($id, $qty, $variant=null)
    {
        $cart_item  =   $this->instance->items->where('id', $id);
//        if (!is_null($variant)) {
//            $cart_item  =   $cart_item->where('variant', $variant);
//        }
        $cart_item  =   $cart_item->first();
        if (is_null($cart_item)) {
            throw new InvalidItemException("This cart does not contain line item {$id}.");
        }
        $cart_item->quantity = $qty; //TODO implement zero and remove
        $cart_item->save();
        $this->refresh();
        return $cart_item;
    }
    public function refresh()
    {
        $this->instance = $this->instance->fresh(['items']);
    }
    public function remove($id)
    {
        $this->instance->items()->where('id', $id)->first()->delete();
        $this->refresh();
    }
    public function removeAll()
    {
        $ids    =   $this->items()->pluck('id')->toArray();

        foreach ($this->instance->items()->whereIn('id', $ids)->get() as $item) {
            $item->delete();
        }
        $this->refresh();
    }
    public function items()
    {
        return $this->instance->items;
    }
    public function content()
    {
        return $this->instance->items;
    }
    public function count()
    {
        return $this->items()->sum('quantity');
    }
    protected function calculateTotal()
    {
        $total  =   0.00;
        foreach ($this->instance->items as $item) {
            $total  +=   $item->total();
        }
        return $total;
    }
    public function total($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        //this is without shipping
//        $total = $content->reduce(function ($total, CartItem $cartItem) {
//            return $total + ($cartItem->qty * $cartItem->priceTax);
//        }, 0);
        $total  =   $this->calculateTotal();
        return $this->numberFormat($total, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function removeDiscount()
    {
        $this->instance->removeDiscount();
    }
    public function setDiscount($amount, $code=null, $type=CartModel::DISCOUNT_TYPE_PERCENTAGE)
    {
        $this->instance->setDiscount($amount, $type, $code);
    }
    public function getDiscountCouponApplied()
    {
        return $this->instance->discount_code;
    }
    public function getDiscountApplied($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        $discount   =   0.00;
        $amount     =   $this->instance->discount_amount;
        if ($this->instance->discount_type==CartModel::DISCOUNT_TYPE_PERCENTAGE) {
            $discount   =   $this->subTotal() * ($amount/100);
        }elseif ($this->instance->discount_type==CartModel::DISCOUNT_TYPE_AMOUNT) {
            $discount   =   $amount;
        }
        $discount   =   $this->numberFormat($discount, $decimals, $decimalPoint, $thousandSeperator);
        return $discount;
    }
    public function subTotal($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        $subTotal   =   $this->calculateTotal();
        return $this->numberFormat($subTotal, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function totalPayable($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        //this return float value not a string so that it can be used for making/charging the payment
        $total  =  $this->subTotal($decimals, $decimalPoint, $thousandSeperator) - $this->getDiscountApplied();
        $total  += $this->shippingCost($decimals, $decimalPoint, $thousandSeperator);
        return $this->numberFormat($total, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function setShippingCost($cost)
    {
        $this->instance->setShippingCost($cost);
    }
    public function shippingCost($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        $shippingCost   =   $this->instance->shipping_cost;
        return $this->numberFormat($shippingCost, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function tax($decimals = null, $decimalPoint = null, $thousandSeperator = null)
    {
        $tax    =   0.00;
        foreach ($this->instance->items as $item)
        {
            $tax  +=   $item->totalTax();
        }
        return $this->numberFormat($tax, $decimals, $decimalPoint, $thousandSeperator);
    }
    /**
     * Get the tax rate from the first item.
     * @return int|float $taxRate
     */
    public function getTaxRate()
    {
        $item   =   $this->instance->items->first();
        return $item->tax_rate;
    }
    /**
     * Set the tax rate for the cart item with the given rowId.
     *
     * @param int    $id
     * @param int|float $taxRate
     * @return void
     */
    public function setTax($id=null, $taxRate=null)
    {
        if (is_null($taxRate)) {
            $taxRate    =   config('laracart.tax_rate', 0.00);
        }
        if (is_null($id)) {
            $this->instance->items()->update(['tax_rate'=> $taxRate]);
        }else{
            //TODO update by attributes i.e. variant and options and product id
        }
    }
    private function createCartItem($id, $variant=null, $qty=1, $price, $tax_rate, array $options)
    {
        $cartItem   = $this->instance->items()->create([
            'product_id'    =>  $id,
            'quantity'      =>  $qty,
            'variant'       =>  $variant,
            'price'         =>  $price,
            'tax_rate'      =>  $tax_rate,
            'options'       =>  $options,
        ]);//'quantity', 'price', 'options'
        return $cartItem;
    }
    private function numberFormat($value, $decimals, $decimalPoint, $thousandSeperator)
    {
        if(is_null($decimals)) {
            $decimals = is_null(config('laracart.format.decimals')) ? 2 : config('laracart.format.decimals');
        }
        if(is_null($decimalPoint)){
            $decimalPoint = is_null(config('laracart.format.decimal_point')) ? '.' : config('laracart.format.decimal_point');
        }
        if(is_null($thousandSeperator)){
            $thousandSeperator = is_null(config('laracart.format.thousand_seperator')) ? ',' : config('laracart.format.thousand_seperator');
        }
        return number_format($value, $decimals, $decimalPoint, $thousandSeperator);
    }
    public function get()
    {
        return $this->instance->items->get()->toArray();
    }
    public function copyItems($cartId)
    {
        $old_cart   =   CartModel::find($cartId);
        $old_cart->load('items');
        foreach ($old_cart->items as $item) {
            $newItem = $item->replicate(['cart_id']);
            $this->instance->items()->save($newItem);
        }
    }
    public function touch()
    {
        $this->instance->touch();
    }
}
