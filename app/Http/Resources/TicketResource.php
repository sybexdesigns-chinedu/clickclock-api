<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
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
            'ticket_number' => $this->ticket_number,
            'type' => $this->type,
            'subject' => $this->subject,
            // 'description' => $this->description,
            // 'priority' => $this->priority,
            'status' => $this->status,
            // 'attachment' => $this->attachment ? asset('attachments/' . $this->attachment) : null,
            'created_at' => $this->created_at->format('M d, Y'),
        ];
    }
}
