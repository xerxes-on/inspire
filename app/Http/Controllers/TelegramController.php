<?php

namespace App\Http\Controllers;

use App\Models\Order;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphBot;
use Illuminate\Http\Request;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Step 1: Get the message from the bot
        $message = $request->input('message');

        if (isset($message['text'])) {
            $incomingMessage = $message['text'];

            // Step 2: Parse the message to extract the order ID
            $orderID = $this->parseOrderFromMessage($incomingMessage);

            // Step 3: Query users from the database with the same order
            $users = Order::where('order_id', $orderID)->with('user')->get();

            // Step 4: Send the message to all users with the same order
            foreach ($users as $order) {
                $userTelegramId = $order->user->telegram_id;

                // Step 5: Send message to user via Telegraph
                $this->sendMessageToUser($userTelegramId, "Order update: ".$incomingMessage);
            }
        }

        return response()->json(['status' => 'success']);
    }

    protected function parseOrderFromMessage($message)
    {
        // Parsing the order ID from the message (this logic can change based on your needs)
        preg_match('/Order ID: (\d+)/', $message, $matches);
        return $matches[1] ?? null;
    }

    protected function sendMessageToUser($telegramId, $message)
    {
        // Get the bot instance
        $bot = TelegraphBot::where('name', 'my_bot_name')->first();  // Adjust bot name accordingly

        // Use the Telegraph package to send a message to the user
        Telegraph::bot($bot)
            ->chat($telegramId)
            ->message($message)
            ->send();
    }
}
