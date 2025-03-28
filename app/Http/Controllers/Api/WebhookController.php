<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'url' => ['required', 'url'],
                'events' => ['required', 'array', 'min:1'],
                'events.*' => ['required', 'string', 'in:transaction.created,transaction.failed,account.created,account.updated,account.deleted,transfer.completed,transfer.failed'],
            ]);

            $webhook = Webhook::create([
                'user_id' => $request->user()->id,
                'url' => $validated['url'],
                'events' => $validated['events'],
                'is_active' => true,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Webhook created successfully',
                'data' => $webhook
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create webhook',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function index(Request $request)
    {
        $webhooks = Webhook::where('user_id', $request->user()->id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $webhooks
        ]);
    }

    public function destroy(Request $request, Webhook $webhook)
    {
        if ($request->user()->id !== $webhook->user_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to delete this webhook'
            ], Response::HTTP_FORBIDDEN);
        }

        $webhook->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Webhook deleted successfully'
        ], Response::HTTP_NO_CONTENT);
    }

    public static function dispatchEvent($event, $data)
    {
        $webhooks = Webhook::where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();

        foreach ($webhooks as $webhook) {
            try {
                Http::post($webhook->url, [
                    'event' => $event,
                    'data' => $data,
                    'timestamp' => now()->toIso8601String(),
                ]);
            } catch (\Exception $e) {
                Log::error('Webhook dispatch failed', [
                    'webhook_id' => $webhook->id,
                    'event' => $event,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
} 