<?php

namespace App\Repositories;

use App\Models\ProductOrder;

class ProductOrderRepository implements ProductOrderRepositoryInterface
{
    protected $model;

    public function __construct(ProductOrder $model)
    {
        $this->model = $model;
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function findById($id)
    {
        return $this->model->with(['product', 'feature', 'invoice', 'subscription', 'client'])->find($id);
    }

    public function getClientOrders($clientId)
    {
        return $this->model->where('client_id', $clientId)
            ->with(['product', 'feature', 'invoice', 'subscription'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPendingOrders()
    {
        return $this->model->where('status', 'pending_payment')
            ->with(['product', 'feature', 'client'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function updateStatus($orderId, $status)
    {
        return $this->model->where('id', $orderId)->update(['status' => $status]);
    }

    public function attachInvoice($orderId, $invoiceId)
    {
        return $this->model->where('id', $orderId)->update(['invoice_id' => $invoiceId]);
    }

    public function attachSubscription($orderId, $subscriptionId)
    {
        return $this->model->where('id', $orderId)->update(['subscription_id' => $subscriptionId]);
    }
}
