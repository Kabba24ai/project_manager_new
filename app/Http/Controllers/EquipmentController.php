<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    /**
     * Get all equipment categories with their equipment items
     */
    public function getEquipment(Request $request)
    {
        $categories = ProductCategory::with(['equipment' => function ($query) {
            $query->orderBy('equipment_name');
        }])->orderBy('title')->get();

        $equipmentCategories = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->title,
                'equipment' => $category->equipment->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->equipment_name,
                        'available' => $item->current_status === 'available',
                    ];
                })
            ];
        });

        return response()->json([
            'equipment_categories' => $equipmentCategories
        ]);
    }

    /**
     * Get all equipment items (flat list)
     */
    public function getAllEquipment(Request $request)
    {
        $equipment = Equipment::with('category')
            ->orderBy('equipment_name')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->equipment_name,
                    'category_id' => $item->product_category_id,
                    'category_name' => $item->category->title ?? null,
                    'available' => $item->current_status === 'available',
                ];
            });

        return response()->json([
            'equipment' => $equipment
        ]);
    }
}
