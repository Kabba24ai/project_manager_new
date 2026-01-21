<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function searchOrders(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['orders' => []]);
        }
        
        // Query orders from the database
        // Using the Order model from Kaaba2 (same database)
        $orders = \DB::table('orders')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->where('orders.order_number', 'like', '%' . $query . '%')
            ->select(
                'orders.id',
                'orders.order_number',
                'orders.customer_id',
                'orders.customer_name',
                'orders.customer_email',
                'orders.customer_phone',
                'orders.company_name',
                'customers.first_name',
                'customers.last_name',
                'customers.email as customer_db_email'
            )
            ->orderBy('orders.order_date', 'desc')
            ->limit(20)
            ->get();
        
        // Format the results
        $formattedOrders = $orders->map(function ($order) {
            // Get products for this order
            $products = \DB::table('order_products')
                ->where('order_id', $order->id)
                ->pluck('product_name')
                ->take(3) // Limit to first 3 products
                ->toArray();
            
            $productDisplay = !empty($products) 
                ? implode(', ', $products) 
                : 'No products';
            
            // If more than 3 products, add indicator
            $productCount = \DB::table('order_products')->where('order_id', $order->id)->count();
            if ($productCount > 3) {
                $productDisplay .= ' (+' . ($productCount - 3) . ' more)';
            }
            
            // Get billing address
            $billingAddress = \DB::table('order_addresses')
                ->where('order_id', $order->id)
                ->where('type', 'Billing')
                ->first();
            
            // Get shipping/delivery address
            $shippingAddress = \DB::table('order_addresses')
                ->where('order_id', $order->id)
                ->where('type', 'Shipping')
                ->first();
            
            // Format billing address
            $billingAddressText = 'N/A';
            if ($billingAddress) {
                $addressParts = array_filter([
                    $billingAddress->address,
                    $billingAddress->city,
                    $billingAddress->state,
                    $billingAddress->zip_code,
                ]);
                $billingAddressText = !empty($addressParts) ? implode(', ', $addressParts) : 'N/A';
            }
            
            // Format shipping/delivery address
            $shippingAddressText = 'N/A';
            if ($shippingAddress) {
                $addressParts = array_filter([
                    $shippingAddress->address,
                    $shippingAddress->city,
                    $shippingAddress->state,
                    $shippingAddress->zip_code,
                ]);
                $shippingAddressText = !empty($addressParts) ? implode(', ', $addressParts) : 'N/A';
            }
            
            // Determine customer name and email
            $customerName = $order->customer_name ?: trim($order->first_name . ' ' . $order->last_name);
            $customerEmail = $order->customer_email ?: $order->customer_db_email;
            
            return [
                'id' => $order->id,
                'orderNumber' => $order->order_number,
                'product' => $productDisplay,
                'billingAddress' => $billingAddressText,
                'shippingAddress' => $shippingAddressText,
                'customer' => [
                    'name' => $customerName,
                    'firstName' => $order->first_name,
                    'lastName' => $order->last_name,
                    'email' => $customerEmail,
                    'phone' => $order->customer_phone,
                    'company' => $order->company_name,
                ],
            ];
        })->values()->toArray();
        
        return response()->json(['orders' => $formattedOrders]);
    }
    
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['customers' => []]);
        }
        
        $customers = Customer::where('status', 'Active')
            ->where(function ($q) use ($query) {
                $q->where('company_name', 'like', '%' . $query . '%')
                  ->orWhere('first_name', 'like', '%' . $query . '%')
                  ->orWhere('last_name', 'like', '%' . $query . '%')
                  ->orWhere('email', 'like', '%' . $query . '%')
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $query . '%']);
            })
            ->orderByRaw("
                CASE 
                    WHEN first_name LIKE ? THEN 1
                    WHEN last_name LIKE ? THEN 2
                    WHEN email LIKE ? THEN 3
                    WHEN company_name LIKE ? THEN 4
                    ELSE 5
                END,
                first_name,
                last_name
            ", [
                $query . '%',  // Exact match at start gets highest priority
                $query . '%',
                $query . '%',
                $query . '%'
            ])
            ->limit(50)
            ->get();
        
        $mapped = $customers->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->full_name,
                    'firstName' => $customer->first_name,
                    'lastName' => $customer->last_name,
                    'company' => $customer->company_name,
                    'email' => $customer->email,
                    'phone' => $customer->phone ?? $customer->company_phone,
                ];
            })->values()->toArray();
        
        return response()->json(['customers' => $mapped]);
    }
    
    public function getOrders($customerId)
    {
        // Query orders for a specific customer from the database
        $orders = \DB::table('orders')
            ->leftJoin('customers', 'orders.customer_id', '=', 'customers.id')
            ->where('orders.customer_id', $customerId)
            ->select(
                'orders.id',
                'orders.order_number',
                'orders.customer_id',
                'orders.customer_name',
                'orders.customer_email',
                'orders.customer_phone',
                'orders.company_name',
                'customers.first_name',
                'customers.last_name',
                'customers.email as customer_db_email'
            )
            ->orderBy('orders.order_date', 'desc')
            ->limit(50)
            ->get();
        
        // Format the results
        $formattedOrders = $orders->map(function ($order) {
            // Get products for this order
            $products = \DB::table('order_products')
                ->where('order_id', $order->id)
                ->pluck('product_name')
                ->take(3)
                ->toArray();
            
            $productDisplay = !empty($products) 
                ? implode(', ', $products) 
                : 'No products';
            
            $productCount = \DB::table('order_products')->where('order_id', $order->id)->count();
            if ($productCount > 3) {
                $productDisplay .= ' (+' . ($productCount - 3) . ' more)';
            }
            
            // Get billing address
            $billingAddress = \DB::table('order_addresses')
                ->where('order_id', $order->id)
                ->where('type', 'Billing')
                ->first();
            
            // Get shipping/delivery address
            $shippingAddress = \DB::table('order_addresses')
                ->where('order_id', $order->id)
                ->where('type', 'Shipping')
                ->first();
            
            // Format billing address
            $billingAddressText = 'N/A';
            if ($billingAddress) {
                $addressParts = array_filter([
                    $billingAddress->address,
                    $billingAddress->city,
                    $billingAddress->state,
                    $billingAddress->zip_code,
                ]);
                $billingAddressText = !empty($addressParts) ? implode(', ', $addressParts) : 'N/A';
            }
            
            // Format shipping/delivery address
            $shippingAddressText = 'N/A';
            if ($shippingAddress) {
                $addressParts = array_filter([
                    $shippingAddress->address,
                    $shippingAddress->city,
                    $shippingAddress->state,
                    $shippingAddress->zip_code,
                ]);
                $shippingAddressText = !empty($addressParts) ? implode(', ', $addressParts) : 'N/A';
            }
            
            $customerName = $order->customer_name ?: trim($order->first_name . ' ' . $order->last_name);
            $customerEmail = $order->customer_email ?: $order->customer_db_email;
            
            return [
                'id' => $order->id,
                'orderNumber' => $order->order_number,
                'product' => $productDisplay,
                'billingAddress' => $billingAddressText,
                'shippingAddress' => $shippingAddressText,
                'customer' => [
                    'name' => $customerName,
                    'firstName' => $order->first_name,
                    'lastName' => $order->last_name,
                    'email' => $customerEmail,
                    'phone' => $order->customer_phone,
                    'company' => $order->company_name,
                ],
            ];
        })->values()->toArray();
        
        return response()->json(['orders' => $formattedOrders]);
    }
}
