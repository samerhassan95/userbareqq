<?php

namespace App\Repositories;

interface ProductOrderRepositoryInterface
{
    public function create(array $data);
    public function findById($id);
    public function getClientOrders($clientId);
    public function getPendingOrders();
    public function updateStatus($orderId, $status);
    public function attachInvoice($orderId, $invoiceId);
    public function attachSubscription($orderId, $subscriptionId);
}
