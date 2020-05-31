<?php

// When an item is sold or bought:
Broadcast::channel('update-shop-inventory-{userId}', function ($user, $userId) {
	return $user->id === (int) $userId;
});


Broadcast::channel('show-crafting-timeout-bar-{userId}', function($user, $userId) {
	return $user->id === (int) $userId;
});