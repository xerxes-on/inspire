<?php

namespace App\Telegram;

use App\Models\Order;
use App\Models\User;
use DateTime;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Stringable;

// Ensure you import the correct Button class


class Handler extends WebhookHandler
{
    public function search(): void
    {
        $path = storage_path('app/message_id.txt');
        if (file_exists($path)) {
            $messageId = file_get_contents($path);
            Telegraph::chat($this->chat_id())
                ->deleteMessage($messageId)
                ->send();
        }
        $response = Telegraph::chat($this->chat_id())
            ->message('Выберите склад или продолжите поиск')
            ->keyboard(
                Keyboard::make()->buttons([
                    Button::make('Wildberries')->action('branch')->param('store', 'Wildberries'),
                ]))->send();
        $chatId = $response['result']['chat']['id'];
        $pathch = storage_path('app/chat_id.txt');
        file_put_contents($pathch, $chatId);

        $messageId = $response['result']['message_id'];
        $path = storage_path('app/message_id.txt');
        file_put_contents($path, $messageId);
    }

    public function branch($store): void
    {

        $pathch = storage_path('app/chat_id.txt');
        $chatId = file_get_contents($pathch);
        $user = User::where('chat_id', $chatId)->first();
        $order = Order::create([
            'user_id' => $user->id,
            'store' => $store,
        ]);
        $path = storage_path('app/message_id.txt');
        if (file_exists($path)) {
            $messageId = file_get_contents($path);
            Telegraph::chat($this->chat_id())
                ->deleteMessage($messageId)
                ->send();
        }
        $response = Telegraph::chat($this->chat_id())
            ->message('Выберите пункт в меню ')
            ->keyboard(Keyboard::make()->buttons([
                Button::make('Казань')->action('type')->param('bn', 'Казань')->param('id', $order->id),
                Button::make('Казань 2')->action('type')->param('bn', 'Казань 2')->param('id', $order->id),
                Button::make('Подольск')->action('type')->param('bn', 'Подольск')->param('id', $order->id),
                Button::make('Подольск 3')->action('type')->param('bn', 'Подольск 3')->param('id', $order->id),
                Button::make('Подольск 4')->action('type')->param('bn', 'Подольск 4')->param('id', $order->id),
                Button::make('Тула')->action('type')->param('bn', 'Тула')->param('id', $order->id),
                Button::make('Электросталь')->action('type')->param('bn', 'Электросталь')->param('id', $order->id),
                Button::make('Коледино')->action('type')->param('bn', 'Коледино')->param('id', $order->id),
                ]))->send();

        $messageId = $response['result']['message_id'];

        $path = storage_path('app/message_id.txt');
        file_put_contents($path, $messageId);
    }

    public function type($bn, $id): void
    {

        Order::where('id', $id)->update(['branch' => $bn]);
        $path = storage_path('app/message_id.txt');
        if (file_exists($path)) {
            $messageId = file_get_contents($path);
            Telegraph::chat($this->chat_id())
                ->deleteMessage($messageId)
                ->send();
        }
        $response = Telegraph::chat($this->chat_id()) // Replace with your actual chat or bot context
        ->message('Выберите пункт в меню')
            ->keyboard(Keyboard::make()->buttons([
                Button::make('Короб')->action('coe')->param('type', 'Короб')->param('id', $id),
                Button::make('Монопаллета')->action('coe')->param('type', 'Монопаллета')->param('id', $id),
                Button::make('Суперсейф')->action('coe')->param('type', 'Суперсейф')->param('id', $id),
                ]))->send();

        $messageId = $response['result']['message_id'];
        file_put_contents($path, $messageId);
    }

    public function coe($type, $id): void
    {
        Order::where('id', $id)->update(['type' => $type]);
        $path = storage_path('app/message_id.txt');
        if (file_exists($path)) {
            $messageId = file_get_contents($path);
            Telegraph::chat($this->chat_id())
                ->deleteMessage($messageId)
                ->send();
        }
        $response = Telegraph::chat($this->chat_id())
            ->message('Выберите тип приёмки, на который будем искать слот
При выборе платной приемки бот будет искать указанный коэффициент или ниже
Например: Выбрано "До x2" - бот ищет: бесплатную, x1 и x2 приемки')
            ->keyboard(Keyboard::make()->buttons([
                Button::make('Бесплатная 🆓')->action('time')->param('id', $id)->param('t', 'Бесплатная'),
                Button::make('до 1x ⬆️')->action('time')->width(0.33)->param('id', $id)->param('t', 'x1'),
                Button::make('до 2x ⬆️')->action('time')->width(0.33)->param('id', $id)->param('t', 'x2'),
                Button::make('до 3x ⬆️')->action('time')->width(0.33)->param('id', $id)->param('t', 'x3'),
                Button::make('до 4x ⬆️')->action('time')->width(0.33)->param('id', $id)->param('t', 'x4'),
                Button::make('до 5x ⬆️')->action('time')->width(0.33)->param('id', $id)->param('t', 'x5'),
                Button::make('до 6x ⬆️')->action('time')->width(0.33)->param('id', $id)->param('t', 'x6'),
                Button::make('до 7x ⬆️')->action('time')->width(0.33)->param('id', $id)->param('t', 'x7'),
                Button::make('до 8x ⬆️')->action('time')->width(0.33)->param('id', $id)->param('t', 'x8'),
                Button::make('до 9x ⬆️')->action('time')->width(0.33)->param('id', $id)->param('t', 'x9'),
                Button::make('до 10x ⬆️')->action('time')->param('t', 'x10')->param('id', $id),

            ]))->send();

        $messageId = $response['result']['message_id'];
        file_put_contents($path, $messageId);
    }

    public function time($t, $id): void
    {
        Order::where('id', $id)->update(['coefficient' => $t]);

        $path = storage_path('app/message_id.txt');
        if (file_exists($path)) {
            $messageId = file_get_contents($path);
            Telegraph::chat($this->chat_id())
                ->deleteMessage($messageId)
                ->send();
        }
        $response = Telegraph::chat($this->chat_id()) // Replace with your actual chat or bot context
        ->message('Выберите даты, когда вам нужны лимиты')
            ->keyboard(Keyboard::make()->buttons([
                Button::make('Сегодня')->action('now')->param('id', $id),
                Button::make('Завтра')->action('tmrw')->param('id', $id),
                Button::make('Неделя (выключая сегодня)')->action('week')->param('id', $id),
                Button::make('Искать, пока не найдется')->action('Искать всегда')->param('id', $id),
                Button::make('Вести даты самостоятельно')->action('custom')->param('id', $id),
            ]))->send();

        $messageId = $response['result']['message_id'];
        file_put_contents($path, $messageId);
    }

    public function now($id): void
    {
        $date = new DateTime();
        $path = storage_path('app/message_id.txt');
        if (file_exists($path)) {
            $messageId = file_get_contents($path);
            Telegraph::chat($this->chat_id())
                ->deleteMessage($messageId)
                ->send();
        }
        Order::where('id', $id)->update(['time' => $date->format('Y-m-d')]);

        $response = Telegraph::chat($this->chat_id())
            ->message('Done 🥳')
            ->keyboard(Keyboard::make()->buttons([
                Button::make('Поиск')->action('search'),
            ]))->send();
        $messageId = $response['result']['message_id'];
    }

    public function tmrw($id): void
    {
        $date = new DateTime();
        $tomorrow = $date->modify('+1 day')->format('Y-m-d');


        $path = storage_path('app/message_id.txt');
        if (file_exists($path)) {
            $messageId = file_get_contents($path);
            Telegraph::chat($this->chat_id())
                ->deleteMessage($messageId)
                ->send();
        }
        Order::where('id', $id)->update(['time' => $tomorrow]);

        $response = Telegraph::chat($this->chat_id())
            ->message('Done 🥳')
            ->keyboard(Keyboard::make()->buttons([
                Button::make('Поиск')->action('search'),
            ]))->send();
        $messageId = $response['result']['message_id'];
    }

    public function week($id): void
    {
        $date = new DateTime();
        $week = $date->modify('+1 week')->format('Y-m-d');

        $path = storage_path('app/message_id.txt');
        if (file_exists($path)) {
            $messageId = file_get_contents($path);
            Telegraph::chat($this->chat_id())
                ->deleteMessage($messageId)
                ->send();
        }
        Order::where('id', $id)->update(['time' => $week]);

        $response = Telegraph::chat($this->chat_id())
            ->message('Done 🥳')
            ->keyboard(Keyboard::make()->buttons([
                Button::make('Поиск')->action('search'),
            ]))->send();
        $messageId = $response['result']['message_id'];
    }

    public function seek($id): void
    {
        Order::where('id', $id)->update(['time' => 'seek']);

        $response = Telegraph::chat($this->chat_id())
            ->message('Done 🥳')
            ->keyboard(Keyboard::make()->buttons([
                Button::make('Поиск')->action('search'),
            ]))->send();
        $messageId = $response['result']['message_id'];
    }

    public function custom($id): void
    {

        $path = storage_path('app/message_id.txt');
        if (file_exists($path)) {
            $messageId = file_get_contents($path);
            Telegraph::chat($this->chat_id())
                ->deleteMessage($messageId)
                ->send();
        }
        $path = storage_path('app/order.txt');
        file_put_contents($path, $id);

        Telegraph::chat($this->chat_id())
            ->message("Введите дату в формате 'YYYY-MM-DD'. Дата должна быть в пределах от сегодняшнего дня до месяца вперёд.")
            ->send();

    }

    public function handleChatMessage(Stringable $text): void
    {
        if (!DateTime::createFromFormat('Y-m-d', $text)) {
            Telegraph::chat($this->chat_id())
                ->sticker(storage_path('app/AnimatedSticker.tgs'))
                ->send();
            return;
        }
        $now = new DateTime();
        $maxDate = new DateTime();
        $maxDate->modify('+1 month');

        $inputDate = DateTime::createFromFormat('Y-m-d', $text);
        if (!$inputDate) {
            Telegraph::chat($this->chat_id())
                ->message("Неверный формат даты. Введите дату в формате 'YYYY-MM-DD'.")
                ->send();
            return;
        }
        if ($inputDate < $now || $inputDate > $maxDate) {
            Telegraph::chat($this->chat_id())
                ->message("Дата должна быть в пределах от сегодняшнего дня до месяца вперёд. Пожалуйста, введите правильную дату.")
                ->send();
            return;
        }
        $path = storage_path('app/order.txt');
        $id = file_get_contents($path);
        Order::where('id', $id)->update(['time' => $inputDate->format('Y-m-d')]);

        $response = Telegraph::chat($this->chat_id())
            ->message('Дата успешно обновлена 🥳')
            ->keyboard(Keyboard::make()->buttons([
                Button::make('Поиск')->action('search'),
            ]))->send();

        $messageId = $response['result']['message_id'];

    }

    public function man(): void
    {
        Log::info('Sent message man');
        Telegraph::chat($this->chat_id())
            ->message("Лимит найден 🎉\n\nПоставка - Короб, Короб\nДата - 2024-10-26\nПриёмка - x1\nЗабронируйте лимит через личный кабинет WB")
            ->send();
        $orders = Order::where('branch', 'Короб')->where('type', 'Короб')
            ->where('time', '2024-10-26')
            ->where('coefficient', 'x10')
            ->get();
        Log::info('orders', ['orders' => $orders->toArray()]);

    }
//    public function menu(Telegraph $telegraph): void
//    {
//        $telegraph->menu()
//            ->button('Start')->url('https://t.me/your_bot?start=start')
//            ->button('Help')->url('https://t.me/your_bot?start=help')
//            ->send();
//        Telegraph::setChatMenuButton()->default()->send(); //restore default
//        Telegraph::setChatMenuButton()->commands()->send(); //show bot commands in menu button
//        Telegraph::setChatMenuButton()->webApp("Web App", "https://my-web.app")->send(); //show start web app button
//    }


    public function handleUnknownCommand(Stringable $text): void
    {
        if ($text == '/start' || $text == '/cancel') {
            $response = Telegraph::chat($this->chat_id())
                ->message("⭐ Я умею автоматически находить и бронировать найденные слоты на складах Wildberries.\n
🟪 На Wildberries я умею находить слоты с бесплатной приёмкой. Или с платной, до подходящего вам платного коэффициента.
            ")->keyboard(
                    Keyboard::make()->buttons([
                        Button::make('Поиск')->action('search'),
                    ])
                )->send();
            $chat_id = $response['result']['chat']['id'];
            Telegraph::chat($this->chat_id())->reactWithEmoji($response['result']['message_id']-1, '🤝')->send();
            $name = $response['result']['chat']['first_name'];
            $userExists = DB::table('users')->where('chat_id', $chat_id)->exists();
            if (!$userExists) {
                DB::table('users')->insert([
                    'name' => $name,
                    'chat_id' => $chat_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $messageId = $response['result']['message_id'];
            $path = storage_path('app/message_id.txt');
            file_put_contents($path, $messageId);
        }
    }

}
