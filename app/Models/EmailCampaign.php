<?php

namespace App\Models;

use App\Enums\CampaignStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailCampaign extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'subject',
        'content',
        'status',
        'segment',
        'recipients_count',
        'sent_count',
        'opened_count',
        'clicked_count',
        'bounced_count',
        'unsubscribed_count',
        'scheduled_at',
        'sent_at',
    ];

    protected $casts = [
        'status' => CampaignStatus::class,
        'recipients_count' => 'integer',
        'sent_count' => 'integer',
        'opened_count' => 'integer',
        'clicked_count' => 'integer',
        'bounced_count' => 'integer',
        'unsubscribed_count' => 'integer',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function scopeDraft($query)
    {
        return $query->where('status', CampaignStatus::Draft);
    }

    public function scopeSent($query)
    {
        return $query->where('status', CampaignStatus::Sent);
    }

    public function getOpenRateAttribute(): float
    {
        if ($this->sent_count === 0) {
            return 0;
        }

        return round(($this->opened_count / $this->sent_count) * 100, 1);
    }

    public function getClickRateAttribute(): float
    {
        if ($this->sent_count === 0) {
            return 0;
        }

        return round(($this->clicked_count / $this->sent_count) * 100, 1);
    }
}
