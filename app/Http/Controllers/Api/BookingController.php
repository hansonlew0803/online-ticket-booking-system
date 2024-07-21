<?php

namespace App\Http\Controllers\Api;

use App\Events\BookingCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * @OA\Info(title="Online Ticket Booking API", version="1.0.0")
 * @OA\Server(url="http://online-ticket-booking-system.test/api")
 */
class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/bookings",
     *     tags={"Bookings"},
     *     summary="Retrieve a list of bookings",
     *     description="Get a list of all bookings in the system.",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="event_id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=1),
     *                     @OA\Property(property="tickets_booked", type="integer", example=2),
     *                     @OA\Property(property="unit_price", type="number", format="float", example=100.00),
     *                     @OA\Property(property="total_price", type="number", format="float", example=200.00),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Internal server error"),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred.")
     *         )
     *     )
     * )
     */
    public function index()
    {
        return response()->json(Booking::where('user_id', Auth::user()->id)->get());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/bookings",
     *     tags={"Bookings"},
     *     summary="Create a new booking",
     *     description="Create a new booking for an event.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="event_id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="tickets_booked", type="integer", example=2),
     *                 @OA\Property(property="unit_price", type="number", format="float", example=100.00),
     *                 @OA\Property(property="total_price", type="number", format="float", example=200.00)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Booking created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="booking_id", type="integer", example=1),
     *             @OA\Property(property="event_id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="tickets_booked", type="integer", example=2),
     *             @OA\Property(property="unit_price", type="number", format="float", example=100.00),
     *             @OA\Property(property="total_price", type="number", format="float", example=200.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid input"),
     *             @OA\Property(property="message", type="string", example="The input data is not valid.")
     *         )
     *     )
     * )
     */
    public function store(StoreBookingRequest $request)
    {
        // The validated data is already handled by StoreBookingRequest
        $validatedData = $request->validated();

        // Find the event
        $event = Event::findOrFail($validatedData['event_id']);

        try {
            // Check if enough tickets are available
            if ($event->total_tickets < $validatedData['tickets_booked']) {
                return response()->json(['error' => 'Not enough tickets available'], 400);
            }

            // Calculate unit price and total price
            $unitPrice = $event->ticket_price;
            $totalPrice = $unitPrice * $validatedData['tickets_booked'];

            // Decrease the number of available tickets
            $event->total_tickets -= $validatedData['tickets_booked'];
            
            $event->save();
            // return response()->json(['message' => 'Event updated successfully.']);

            // Create the booking
            $booking = Booking::create([
                'user_id' => Auth::id(),
                'event_id' => $validatedData['event_id'],
                'tickets_booked' => $validatedData['tickets_booked'],
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice
            ]);

            event(new BookingCreated($booking)); // Dispatch the event

            return response()->json($booking, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     path="/api/bookings/{id}",
     *     tags={"Bookings"},
     *     summary="Get booking details",
     *     description="Retrieve the details of a specific booking including event information.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Booking ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="booking_id", type="integer", example=1),
     *             @OA\Property(property="event_name", type="string", example="Concert"),
     *             @OA\Property(property="event_description", type="string", example="Concert Details"),
     *             @OA\Property(property="tickets_booked", type="integer", example=2),
     *             @OA\Property(property="unit_price", type="number", format="float", example=100.00),
     *             @OA\Property(property="total_price", type="number", format="float", example=200.00),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            // Find the booking
            $booking = Booking::where('user_id', Auth::id())->findOrFail($id);

            // Prepare the response
            $response = [
                'id' => $booking->id,
                'event_id' => $booking->event->id,
                'event_name' => $booking->event->name,
                'event_description' => $booking->event->description,
                'tickets_booked' => $booking->tickets_booked,
                'unit_price' => (float)$booking->event->ticket_price,
                'total_price' => $booking->event->ticket_price * $booking->tickets_booked,
            ];

            return response()->json($response);
        } catch (ModelNotFoundException $e) {
            // Handle the case where the booking is not found
            return response()->json([
                'error' => 'Booking not found',
                'message' => $e->getMessage()
            ], 404);
        }
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/api/bookings/{id}",
     *     tags={"Bookings"},
     *     summary="Update an existing booking",
     *     description="Update the details of an existing booking.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Booking ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="tickets_booked", type="integer", example=3)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="event_id", type="integer", example=1),
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="tickets_booked", type="integer", example=3),
     *             @OA\Property(property="unit_price", type="number", format="float", example=120.00),
     *             @OA\Property(property="total_price", type="number", format="float", example=360.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Booking not found"),
     *             @OA\Property(property="message", type="string", example="No booking found with the provided ID.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid input"),
     *             @OA\Property(property="message", type="string", example="The input data is not valid.")
     *         )
     *     )
     * )
     */
    public function update(StoreBookingRequest $request, string $id)
    {
        // The validated data is already handled by StoreBookingRequest
        $validatedData = $request->validated();

        // Find and update the booking
        $booking = Booking::where('user_id', Auth::id())->findOrFail($id);

        // Find the event
        $event = Event::findOrFail($booking->event_id);

        try {
            // Calculate the difference in ticket numbers
            $ticketDifference = $validatedData['tickets_booked'] - $booking->tickets_booked;

            // Check if enough tickets are available if increasing the number of tickets
            if ($ticketDifference > 0 && $event->total_tickets < $ticketDifference) {
                return response()->json(['error' => 'Not enough tickets available'], 400);
            }

            // Calculate unit price and total price
            $unitPrice = $booking->unit_price;
            $totalPrice = $unitPrice * $validatedData['tickets_booked'];

            // Update the number of available tickets
            $event->total_tickets -= $ticketDifference;

            $event->save();
            // return response()->json(['message' => 'Event updated successfully.']);

            $booking->update([
                'ticket_booked' => $validatedData['tickets_booked'],
                'total_price' => $totalPrice
            ]);

            return response()->json($booking);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/bookings/{id}",
     *     tags={"Bookings"},
     *     summary="Delete a booking",
     *     description="Remove a booking from the system.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Booking ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Booking deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Booking deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Booking not found"),
     *             @OA\Property(property="message", type="string", example="No booking found with the provided ID.")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        // Find and delete the booking
        $booking = Booking::where('user_id', Auth::id())->findOrFail($id);

        // Find the event
        $event = Event::findOrFail($booking->event_id);

        try {
            // Restore the number of available tickets
            $event->total_tickets += $booking->tickets_booked;

            $event->save();
            // return response()->json(['message' => 'Event updated successfully.']);

            $booking->delete();

            return response()->json(['message' => 'Booking deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }
    }
}
