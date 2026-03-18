<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;
use Mary\Traits\Toast;

class Show extends Component
{
    use Toast;

    public Product $product;

    public function mount(Product $product): void
    {
        // Bloquer les non-admins sur les produits sextoys
        if (!auth()->user()->isAdmin() && $product->productModel->category->value === 'sextoys') {
            abort(403);
        }

        $this->product = $product->load([
            'productModel.brand',
            'supplier',
            'createdBy',
            'updatedBy',
            'stockMovements' => fn($q) => $q->orderBy('created_at', 'desc')->limit(10),
            'priceHistory'   => fn($q) => $q->orderBy('created_at', 'desc')->limit(5),
        ]);
    }

    public function render()
    {
        return view('livewire.products.show')
            ->layout('layouts.app', ['title' => 'Détail produit — ' . $this->product->identifier]);
    }
}
