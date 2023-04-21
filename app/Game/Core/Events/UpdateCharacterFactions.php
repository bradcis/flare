<?php

namespace App\Game\Core\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
Use App\Flare\Models\User;

class UpdateCharacterFactions implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $factions;

    /**
     * @param User $user
     */
    private $user;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param array $marketListings
     * @param int $characterGold
     * @return void
     */
    public function __construct(User $user, Collection $factions)
    {
        $this->factions = $factions;
        $this->user     = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('update-factions-' . $this->user->id);
    }
}
