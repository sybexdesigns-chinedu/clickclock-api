<?php

use App\Models\Gift;
use App\Models\Interest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MusicController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WebhookController;
use App\Http\Middleware\VerifyPaypalWebhook;
use App\Http\Controllers\LivestreamController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\CommentReplyController;
use App\Http\Controllers\NotificationController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::patch('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-email-token', [AuthController::class, 'resendEmailToken']);
    Route::post('/send-reset-token', [AuthController::class, 'sendPasswordResetToken']);
    Route::patch('/reset-password', [AuthController::class, 'resetPassword']);
});

Route::get('payment/paypal/success', [PaymentController::class, 'paypalPaymentSuccess'])->name('payment.paypal.success');
Route::get('payment/paypal/cancel', [PaymentController::class, 'paypalPaymentCancel'])->name('payment.paypal.cancel');
Route::post('payment/paypal/payouts', [PaymentController::class, 'paypalPayouts']);

Route::get('payment/paystack/success', [PaymentController::class, 'paystackPaymentSuccess'])->name('payment.paystack.success');
Route::get('payment/paystack/cancel', [PaymentController::class, 'paystackPaymentCancel'])->name('payment.paystack.cancel');

Route::get('payment/stripe/success', [PaymentController::class, 'stripePaymentSuccess'])->name('payment.stripe.success');
Route::get('payment/stripe/cancel', [PaymentController::class, 'stripePaymentCancel'])->name('payment.stripe.cancel');

Route::post('paypal/webhooks', [WebhookController::class, 'paypalWebhooks'])->middleware(VerifyPaypalWebhook::class);
Route::post('stripe/webhooks', [WebhookController::class, 'stripeWebhooks']);
Route::post('sightengine/video-moderation/callback', function (Request $request) {
    $data = $request->all();
    Log::info('Sightengine Video Moderation Callback', $data);
    return response()->noContent();
})->name('sightengine.video-moderation.callback');

ROute::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('gift', function () {
        return Gift::orderBy('category')
            ->get()
            ->map(fn ($gift) => [
                'id' => $gift->id,
                'name' => $gift->name,
                'price' => $gift->price,
                'category' => $gift->category,
                'icon' => asset('storage/gifts/'.$gift->icon)
            ]);
    });

    Route::get('interest', function () {
        return Interest::orderBy('name')->get(['id', 'name', 'icon']);
    });

    Route::get('interest/list', function () {
        return Interest::orderBy('name')
            ->get()
            ->map(fn ($interest) => [
                'id' => $interest->id,
                'name' => $interest->name,
                'description' => $interest->description,
                'icon' => $interest->icon,
                'no_of_users' => formatNumber($interest->users()->count())
            ]);
    });

    Route::get('leaderboard', [LeaderboardController::class, 'getLeaderboard']);

    Route::get('livestream/{livestream}', [LivestreamController::class, 'show']);
    Route::post('livestream', [LivestreamController::class, 'store']);
    Route::post('livestream/gift/{livestream}', [LivestreamController::class, 'sendGift']);
    Route::post('livestream/comment/{livestream}', [LivestreamController::class, 'comment']);
    Route::put('livestream/like/{livestream}', [LivestreamController::class, 'like']);
    Route::get('livestream/comments/{livestream}', [LivestreamController::class, 'getComments']);
    Route::post('livestream/viewer-joins/{livestream}', [LivestreamController::class, 'viewerJoins']);
    Route::put('livestream/viewer-leaves/{livestream}', [LivestreamController::class, 'viewerLeaves']);
    Route::put('livestream/remove-viewer/{livestream}', [LivestreamController::class, 'removeViewer']);
    Route::put('livestream/change-moderator/{livestream}', [LivestreamController::class, 'changeModerator']);
    Route::post('livestream/send-request/{livestream}', [LivestreamController::class, 'sendRequest']);
    Route::put('livestream/accept-request/{livestream}', [LivestreamController::class, 'acceptRequest']);
    Route::put('livestream/end/{livestream}', [LivestreamController::class, 'endLivestream']);

    Route::get('music/tracklist', [MusicController::class, 'tracklist']);
    Route::get('music/search', [MusicController::class, 'search']);

    Route::get('notification', [NotificationController::class, 'index']);
    Route::post('notification', [NotificationController::class, 'store']);
    Route::get('notification/{notification}', [NotificationController::class, 'show']);
    Route::put('notification/mark-as-read/{notification}', [NotificationController::class, 'markAsRead']);
    Route::put('notification/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    Route::delete('notification/{notification}', [NotificationController::class, 'delete']);

    Route::get('payment/plans', [PaymentController::class, 'getPlans']);
    Route::post('payment/paypal/create', [PaymentController::class, 'createPaypalPayment']);
    Route::post('payment/paystack/create', [PaymentController::class, 'createPaystackPayment']);
    Route::post('payment/stripe/create', [PaymentController::class, 'createStripePayment']);

    Route::get('post/{id}/reels', [PostController::class, 'getReels']);
    Route::get('post/getTopHashtags', [PostController::class, 'getTopHashtags']);
    Route::post('post/like/{post}', [PostController::class, 'like']);
    Route::put('post/unlike/{post}', [PostController::class, 'unlike']);

    Route::post('profile/check-username', [ProfileController::class, 'checkUsername']);
    Route::put('profile/change-password', [ProfileController::class, 'changePassword']);
    Route::put('profile/change-image', [ProfileController::class, 'changeImage']);
    Route::get('profile/top-broadcasters', [ProfileController::class, 'getTopBroadcasters']);

    Route::get('user', [UserController::class, 'index']);
    Route::post('user/follow/{id}', [UserController::class, 'follow']);
    Route::delete('user/unfollow/{id}', [UserController::class, 'unfollow']);
    Route::delete('user/logout', [UserController::class, 'logout']);

    Route::resource('comment', CommentController::class);
    Route::resource('post', PostController::class);
    Route::resource('profile', ProfileController::class);
    Route::resource('reply', CommentReplyController::class);
    Route::resource('status', StatusController::class);
    Route::resource('ticket', TicketController::class);
    // Route::resource('user', UserController::class);
});
