<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MilestoneTemplate extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'milestones' => 'array', // JSON array of milestone definitions
    ];

    /**
     * Get milestone templates for a business type
     */
    public static function getTemplatesForBusinessType($businessType)
    {
        return self::where('business_type', $businessType)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get default template for a business type
     */
    public static function getDefaultTemplate($businessType)
    {
        return self::where('business_type', $businessType)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Generate milestones from template
     */
    public function generateMilestones($escrow, $totalAmount)
    {
        $milestones = [];
        $templateMilestones = $this->milestones ?? [];
        
        foreach ($templateMilestones as $index => $template) {
            $amount = isset($template['percentage']) 
                ? ($totalAmount * $template['percentage'] / 100)
                : ($template['amount'] ?? 0);
            
            $milestones[] = [
                'escrow_id' => $escrow->id,
                'user_id' => $escrow->seller_id,
                'requested_by' => 'seller',
                'milestone_type' => $template['type'] ?? null,
                'note' => $template['note'] ?? $template['title'] ?? '',
                'amount' => $amount,
                'sort_order' => $index + 1,
                'approval_status' => 'pending',
                'approved_by_seller' => true, // Seller auto-approves their own proposal
                'approved_by_buyer' => false,
                'payment_status' => \App\Constants\Status::MILESTONE_UNFUNDED,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        return $milestones;
    }
}

