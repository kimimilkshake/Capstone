<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==========================
        // BASE TABLES
        // ==========================
        Schema::create('admin', function (Blueprint $table) {
            $table->id('admin_id');
            $table->string('admin_name', 50);
            $table->string('admin_user', 10);
            $table->string('admin_password');
            $table->timestamps();
        });

        Schema::create('passenger', function (Blueprint $table) {
            $table->id('passenger_id');
            $table->string('passenger_firstname', 50);
            $table->char('passenger_midinitial', 1)->nullable();
            $table->string('passenger_lastname', 50);
            $table->string('passenger_suffix')->nullable();
            $table->integer('passenger_age');
            $table->enum('passenger_gender', ['M', 'F']);
            $table->enum('passenger_type', [
                'adult',
                'student',
                'minor',
                'uniformed personnel',
                'senior',
                'PWD'
            ]);
            $table->string('passenger_address', 255);
            $table->string('passenger_contactno');
            $table->string('passenger_email')->nullable();
            $table->string('passenger_idnumber')->nullable();
            $table->timestamps();
        });

        Schema::create('staff', function (Blueprint $table) {
            $table->id('staff_id');
            $table->foreignId('admin_id')->constrained('admin', 'admin_id');
            $table->string('staff_name', 50);
            $table->string('staff_user', 10);
            $table->string('staff_password');
            $table->date('staff_dob')->nullable();
            $table->enum('staff_gender', ['M', 'F']);
            $table->string('staff_email');
            $table->enum('staff_status', ['Active', 'Inactive']);
            $table->timestamps();
        });

        Schema::create('promo', function (Blueprint $table) {
            $table->id('promo_id');
            $table->enum('promo_type', ['Discount', 'Freebie']);
            $table->string('promo_name', 50);
            $table->string('promo_code');
            $table->string('promo_description', 255);
            $table->date('promo_start_date');
            $table->date('promo_end_date');
            $table->enum('promo_status', ['Active', 'Inactive']);
            $table->decimal('promo_discount_rate', 5, 2);
            $table->timestamps();
        });

        Schema::create('sender', function (Blueprint $table) {
            $table->id('sender_id');
            $table->string('sender_name');
            $table->string('sender_contactno');
            $table->string('sender_email')->nullable();
            $table->timestamps();
        });

        Schema::create('consignee', function (Blueprint $table) {
            $table->id('consignee_id');
            $table->string('consignee_name');
            $table->string('consignee_contactno');
            $table->timestamps();
        });


        // ==========================
        // VESSEL RELATED
        // ==========================
        Schema::create('vessel', function (Blueprint $table) {
            $table->id('vessel_id');
            $table->foreignId('admin_id')->constrained('admin', 'admin_id');
            $table->text('vessel_code');
            $table->string('vessel_name');
            $table->integer('vessel_total_passenger_capacity');
            $table->text('vessel_cot_plan_url')->nullable();
            $table->enum('vessel_status', ['Active', 'Inactive']);
            $table->timestamps();
        });

        Schema::create('accommodation', function (Blueprint $table) {
            $table->id('accommodation_id');
            $table->foreignId('vessel_id')->constrained('vessel', 'vessel_id');
            $table->string('accommodation_name');
            $table->decimal('accommodation_regular_price', 10, 2);
            $table->integer('accommodation_capacity');
            $table->timestamps();
        });

        Schema::create('hatch', function (Blueprint $table) {
            $table->id('hatch_id');
            $table->foreignId('vessel_id')->constrained('vessel', 'vessel_id');
            $table->string('hatch_label');
            $table->decimal('hatch_length', 8, 2);
            $table->decimal('hatch_width', 8, 2);
            $table->decimal('hatch_height', 8, 2);
            $table->decimal('hatch_weight_capacity', 5, 2)->nullable(); // percentage, optional
            $table->decimal('hatch_area_capacity', 8, 2);
            $table->decimal('hatch_capacity_per_hold', 8, 2);
            $table->timestamps();
        });


        // ==========================
        // BOOKING & PAYMENT
        // ==========================
        Schema::create('booking', function (Blueprint $table) {
            $table->id('booking_ref_no');
            $table->timestamp('booking_date');
            $table->enum('booking_status', ['Confirmed', 'Pending', 'Canceled', 'Refunded']);
            $table->timestamps();
        });

        Schema::create('payment', function (Blueprint $table) {
            $table->id('payment_id');
            $table->foreignId('booking_ref_no')->nullable()->constrained('booking', 'booking_ref_no');
            $table->enum('mode_of_payment', ['Cash', 'Gcash']);
            $table->timestamp('payment_date');
            $table->decimal('total_amount', 10, 2);
            $table->enum('payment_status', ['Completed', 'Pending', 'Canceled']);
            $table->string('transaction_code')->nullable();
            $table->timestamps();
        });

        // ==========================
        // VOYAGES & TICKETS
        // ==========================
        Schema::create('route_port', function (Blueprint $table) {
            $table->id('route_port_id');
            $table->string('route_origin');
            $table->string('route_destination');
            $table->string('port_origin_name');
            $table->string('port_origin_city');
            $table->string('port_origin_province');
            $table->string('port_destination_name');
            $table->string('port_destination_city');
            $table->string('port_destination_province');
            $table->timestamps();
        });

        Schema::create('voyage', function (Blueprint $table) {
            $table->id('voyage_id');
            $table->foreignId('vessel_id')->constrained('vessel', 'vessel_id');
            $table->foreignId('route_port_id')->constrained('route_port', 'route_port_id');
            $table->date('voyage_departure_date');
            $table->date('voyage_arrival_date');
            $table->string('voyage_code')->unique();
            $table->time('voyage_estimated_TD');
            $table->time('voyage_estimated_TA');
            $table->time('voyage_actual_TD')->nullable();
            $table->time('voyage_actual_TA')->nullable();
            $table->enum('voyage_status', ['Scheduled', 'At Sea', 'Completed', 'Cancelled', 'Archived'])
                ->default('Scheduled');
            $table->string('voyage_description', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('passenger_ticket', function (Blueprint $table) {
            $table->id('passenger_ticket_id');
            $table->foreignId('passenger_id')->constrained('passenger', 'passenger_id');
            $table->foreignId('voyage_id')->constrained('voyage', 'voyage_id');
            $table->foreignId('promo_id')->nullable()->constrained('promo', 'promo_id');
            $table->foreignId('payment_id')->nullable()->constrained('payment', 'payment_id');
            $table->foreignId('booking_ref_no')->nullable()->constrained('booking', 'booking_ref_no');
            $table->date('pt_valid_until');
            $table->integer('pt_cot_no');
            $table->decimal('pt_ticket_price', 10, 2);
            $table->timestamps();
        });

        // ==========================
        // CARGO
        // ==========================
        Schema::create('cargo_item', function (Blueprint $table) {
            $table->id('cargo_item_id');
            $table->string('cargo_item_classification');
            $table->string('cargo_item_description');
            $table->decimal('cargo_item_freight', 10, 2);
            $table->decimal('cargo_item_arrastre', 10, 2);
            $table->foreignId('route_port_id')->constrained('route_port', 'route_port_id');
            $table->timestamps();
        });

        Schema::create('cargo_receipt', function (Blueprint $table) {
            $table->id('cargo_receipt_id');
            $table->foreignId('sender_id')->constrained('sender', 'sender_id');
            $table->foreignId('consignee_id')->constrained('consignee', 'consignee_id');
            $table->foreignId('cargo_item_id')->constrained('cargo_item', 'cargo_item_id');
            $table->foreignId('voyage_id')->constrained('voyage', 'voyage_id');
            $table->foreignId('payment_id')->nullable()->constrained('payment', 'payment_id');
            $table->foreignId('booking_ref_no')->nullable()->constrained('booking', 'booking_ref_no');
            $table->integer('cargo_item_qty');
            $table->timestamps();
        });

        Schema::create('bill_of_lading', function (Blueprint $table) {
            $table->id('bill_of_lading_id');
            $table->foreignId('cargo_receipt_id')->constrained('cargo_receipt', 'cargo_receipt_id');
            $table->foreignId('staff_id')->constrained('staff', 'staff_id');
            $table->date('bl_date_issued');
            $table->string('bl_loading_port');
            $table->string('bl_unloading_port');
            $table->timestamps();
        });

        // ==========================
        // MANIFEST
        // ==========================
        Schema::create('manifest', function (Blueprint $table) {
            $table->id('manifest_id');
            $table->foreignId('staff_id')->constrained('staff', 'staff_id');
            $table->foreignId('voyage_id')->constrained('voyage', 'voyage_id');
            $table->enum('manifest_type', ['passenger', 'cargo']);
            $table->date('manifest_date');
            $table->timestamp('manifest_date_generated');
            $table->foreignId('passenger_ticket_id')->nullable()->constrained('passenger_ticket', 'passenger_ticket_id');
            $table->unsignedBigInteger('cargo_ticket_id')->nullable();
            $table->timestamps();
        });

        // ==========================
        // REPRINT
        // ==========================
        Schema::create('reprint', function (Blueprint $table) {
            $table->id('reprint_id');
            $table->foreignId('passenger_ticket_id')->nullable()->constrained('passenger_ticket', 'passenger_ticket_id');
            $table->foreignId('cargo_receipt_id')->nullable()->constrained('cargo_receipt', 'cargo_receipt_id');
            $table->foreignId('staff_id')->constrained('staff', 'staff_id');
            $table->enum('reprint_ticket_type', ['passenger ticket', 'cargo receipt']);
            $table->string('reprint_ticket_reason', 255);
            $table->integer('reprint_ticket_counter');
            $table->timestamp('reprint_ticket_date');
            $table->timestamps();
        });

        // ==========================
        // NOTIFICATIONS
        // ==========================
        Schema::create('notification', function (Blueprint $table) {
            $table->id('notification_id');
            $table->foreignId('cargo_receipt_id')->nullable()->constrained('cargo_receipt', 'cargo_receipt_id');
            $table->foreignId('payment_id')->nullable()->constrained('payment', 'payment_id');
            $table->string('notification_message', 255);
            $table->enum('notification_type', ['Cargo Booking Approval', 'Payment Received']);
            $table->enum('notification_status', ['Approved', 'Rejected', 'Read', 'Archived']);
            $table->timestamp('notification_created');
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('notification');
        Schema::dropIfExists('reprint');
        Schema::dropIfExists('manifest');
        Schema::dropIfExists('bill_of_lading');
        Schema::dropIfExists('cargo_receipt');
        Schema::dropIfExists('passenger_ticket');
        Schema::dropIfExists('voyage');
        Schema::dropIfExists('payment');
        Schema::dropIfExists('booking');
        Schema::dropIfExists('hatch');
        Schema::dropIfExists('accommodation');
        Schema::dropIfExists('vessel');
        Schema::dropIfExists('cargo_item');
        Schema::dropIfExists('consignee');
        Schema::dropIfExists('sender');
        Schema::dropIfExists('promo');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('passenger');
        Schema::dropIfExists('admin');
    }
};
