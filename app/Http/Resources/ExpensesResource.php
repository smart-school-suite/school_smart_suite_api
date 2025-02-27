<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpensesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'expense_name' => $this->expense_name,
            'expense_amount' => $this->amount,
            'expense_description' => $this->description,
            'category' => $this->schoolexpensescategory->name,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at
        ];
    }
}
