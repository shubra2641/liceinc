<?php

namespace Tests\Unit\Models;

use App\Models\Invoice;
use App\Models\License;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Test suite for Ticket model.
 */
class TicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Log is already configured for testing
    }

    /**
     * Test ticket creation.
     */
    public function test_can_create_ticket(): void
    {
        $user = User::factory()->create();
        $category = TicketCategory::factory()->create();

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'subject' => 'Test Ticket Subject',
            'content' => 'Test ticket content',
            'priority' => 'high',
            'status' => 'open',
        ]);

        $this->assertInstanceOf(Ticket::class, $ticket);
        $this->assertEquals($user->id, $ticket->user_id);
        $this->assertEquals('Test Ticket Subject', $ticket->subject);
        $this->assertEquals('high', $ticket->priority);
        $this->assertEquals('open', $ticket->status);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Support ticket created') &&
                   $context['subject'] === 'Test Ticket Subject';
        });
    }

    /**
     * Test default values in boot method.
     */
    public function test_default_values_in_boot(): void
    {
        $user = User::factory()->create();

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'subject' => 'Test Subject',
            'content' => 'Test content',
            // No priority or status set
        ]);

        $this->assertEquals('medium', $ticket->priority);
        $this->assertEquals('open', $ticket->status);
    }

    /**
     * Test user relationship.
     */
    public function test_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $ticket->user);
        $this->assertEquals($user->id, $ticket->user->id);
    }

    /**
     * Test license relationship.
     */
    public function test_belongs_to_license(): void
    {
        $license = License::factory()->create();
        $ticket = Ticket::factory()->create(['license_id' => $license->id]);

        $this->assertInstanceOf(License::class, $ticket->license);
        $this->assertEquals($license->id, $ticket->license->id);
    }

    /**
     * Test invoice relationship.
     */
    public function test_belongs_to_invoice(): void
    {
        $invoice = Invoice::factory()->create();
        $ticket = Ticket::factory()->create(['invoice_id' => $invoice->id]);

        $this->assertInstanceOf(Invoice::class, $ticket->invoice);
        $this->assertEquals($invoice->id, $ticket->invoice->id);
    }

    /**
     * Test category relationship.
     */
    public function test_belongs_to_category(): void
    {
        $category = TicketCategory::factory()->create();
        $ticket = Ticket::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(TicketCategory::class, $ticket->category);
        $this->assertEquals($category->id, $ticket->category->id);
    }

    /**
     * Test replies relationship.
     */
    public function test_has_many_replies(): void
    {
        $ticket = Ticket::factory()->create();
        $reply1 = TicketReply::factory()->create(['ticket_id' => $ticket->id]);
        $reply2 = TicketReply::factory()->create(['ticket_id' => $ticket->id]);

        $this->assertCount(2, $ticket->replies);
        $this->assertTrue($ticket->replies->contains($reply1));
        $this->assertTrue($ticket->replies->contains($reply2));
    }

    /**
     * Test status check methods.
     */
    public function test_status_check_methods(): void
    {
        $openTicket = Ticket::factory()->create(['status' => 'open']);
        $pendingTicket = Ticket::factory()->create(['status' => 'pending']);
        $resolvedTicket = Ticket::factory()->create(['status' => 'resolved']);
        $closedTicket = Ticket::factory()->create(['status' => 'closed']);

        $this->assertTrue($openTicket->isOpen());
        $this->assertTrue($pendingTicket->isPending());
        $this->assertTrue($resolvedTicket->isResolved());
        $this->assertTrue($closedTicket->isClosed());

        $this->assertFalse($openTicket->isPending());
        $this->assertFalse($pendingTicket->isResolved());
        $this->assertFalse($resolvedTicket->isClosed());
        $this->assertFalse($closedTicket->isOpen());
    }

    /**
     * Test priority check methods.
     */
    public function test_priority_check_methods(): void
    {
        $urgentTicket = Ticket::factory()->create(['priority' => 'urgent']);
        $highTicket = Ticket::factory()->create(['priority' => 'high']);
        $mediumTicket = Ticket::factory()->create(['priority' => 'medium']);

        $this->assertTrue($urgentTicket->isUrgent());
        $this->assertTrue($highTicket->isHighPriority());
        $this->assertFalse($mediumTicket->isUrgent());
        $this->assertFalse($mediumTicket->isHighPriority());
    }

    /**
     * Test badge classes and labels.
     */
    public function test_badge_classes_and_labels(): void
    {
        $openTicket = Ticket::factory()->create(['status' => 'open']);
        $pendingTicket = Ticket::factory()->create(['status' => 'pending']);
        $resolvedTicket = Ticket::factory()->create(['status' => 'resolved']);
        $closedTicket = Ticket::factory()->create(['status' => 'closed']);

        // Status badges
        $this->assertEquals('badge-warning', $openTicket->status_badge_class);
        $this->assertEquals('badge-info', $pendingTicket->status_badge_class);
        $this->assertEquals('badge-success', $resolvedTicket->status_badge_class);
        $this->assertEquals('badge-secondary', $closedTicket->status_badge_class);

        // Status labels
        $this->assertEquals('Open', $openTicket->status_label);
        $this->assertEquals('Pending', $pendingTicket->status_label);
        $this->assertEquals('Resolved', $resolvedTicket->status_label);
        $this->assertEquals('Closed', $closedTicket->status_label);

        $lowTicket = Ticket::factory()->create(['priority' => 'low']);
        $mediumTicket = Ticket::factory()->create(['priority' => 'medium']);
        $highTicket = Ticket::factory()->create(['priority' => 'high']);
        $urgentTicket = Ticket::factory()->create(['priority' => 'urgent']);

        // Priority badges
        $this->assertEquals('badge-secondary', $lowTicket->priority_badge_class);
        $this->assertEquals('badge-info', $mediumTicket->priority_badge_class);
        $this->assertEquals('badge-warning', $highTicket->priority_badge_class);
        $this->assertEquals('badge-danger', $urgentTicket->priority_badge_class);

        // Priority labels
        $this->assertEquals('Low', $lowTicket->priority_label);
        $this->assertEquals('Medium', $mediumTicket->priority_label);
        $this->assertEquals('High', $highTicket->priority_label);
        $this->assertEquals('Urgent', $urgentTicket->priority_label);
    }

    /**
     * Test replies count attribute.
     */
    public function test_replies_count_attribute(): void
    {
        $ticket = Ticket::factory()->create();

        TicketReply::factory()->create(['ticket_id' => $ticket->id]);
        TicketReply::factory()->create(['ticket_id' => $ticket->id]);
        TicketReply::factory()->create(['ticket_id' => $ticket->id]);

        $this->assertEquals(3, $ticket->replies_count);
    }

    /**
     * Test days since created attribute.
     */
    public function test_days_since_created_attribute(): void
    {
        $ticket = Ticket::factory()->create(['created_at' => now()->subDays(5)]);
        $this->assertEquals(5, $ticket->days_since_created);
    }

    /**
     * Test scopes.
     */
    public function test_scopes(): void
    {
        Ticket::factory()->create(['status' => 'open', 'priority' => 'high', 'user_id' => 1]);
        Ticket::factory()->create(['status' => 'pending', 'priority' => 'medium', 'user_id' => 1]);
        Ticket::factory()->create(['status' => 'resolved', 'priority' => 'low', 'user_id' => 2]);
        Ticket::factory()->create(['status' => 'closed', 'priority' => 'urgent', 'user_id' => 2]);

        $this->assertCount(1, Ticket::open()->get());
        $this->assertCount(1, Ticket::pending()->get());
        $this->assertCount(1, Ticket::resolved()->get());
        $this->assertCount(1, Ticket::closed()->get());
        $this->assertCount(2, Ticket::forUser(1)->get());
        $this->assertCount(1, Ticket::byPriority('high')->get());
    }

    /**
     * Test search scope.
     */
    public function test_search_scope(): void
    {
        Ticket::factory()->create(['subject' => 'Login Issue', 'content' => 'Cannot login to system']);
        Ticket::factory()->create(['subject' => 'Payment Problem', 'content' => 'Payment not working']);
        Ticket::factory()->create(['subject' => 'Feature Request', 'content' => 'Need new feature']);

        $results = Ticket::search('login')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Login Issue', $results->first()->subject);

        $results = Ticket::search('payment')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Payment Problem', $results->first()->subject);
    }

    /**
     * Test mark as resolved.
     */
    public function test_mark_as_resolved(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'open']);

        $result = $ticket->markAsResolved();
        $this->assertTrue($result);
        $this->assertEquals('resolved', $ticket->fresh()->status);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Support ticket marked as resolved');
        });
    }

    /**
     * Test mark as closed.
     */
    public function test_mark_as_closed(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'resolved']);

        $result = $ticket->markAsClosed();
        $this->assertTrue($result);
        $this->assertEquals('closed', $ticket->fresh()->status);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Support ticket marked as closed');
        });
    }

    /**
     * Test reopen ticket.
     */
    public function test_reopen_ticket(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'closed']);

        $result = $ticket->reopen();
        $this->assertTrue($result);
        $this->assertEquals('open', $ticket->fresh()->status);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Support ticket reopened');
        });
    }

    /**
     * Test statistics.
     */
    public function test_statistics(): void
    {
        Ticket::factory()->create(['status' => 'open', 'priority' => 'high']);
        Ticket::factory()->create(['status' => 'pending', 'priority' => 'medium']);
        Ticket::factory()->create(['status' => 'resolved', 'priority' => 'low']);
        Ticket::factory()->create(['status' => 'closed', 'priority' => 'urgent']);

        $statistics = Ticket::getStatistics();

        $this->assertArrayHasKey('total', $statistics);
        $this->assertArrayHasKey('open', $statistics);
        $this->assertArrayHasKey('pending', $statistics);
        $this->assertArrayHasKey('resolved', $statistics);
        $this->assertArrayHasKey('closed', $statistics);
        $this->assertArrayHasKey('by_status', $statistics);
        $this->assertArrayHasKey('by_priority', $statistics);

        $this->assertEquals(4, $statistics['total']);
        $this->assertEquals(1, $statistics['open']);
        $this->assertEquals(1, $statistics['pending']);
        $this->assertEquals(1, $statistics['resolved']);
        $this->assertEquals(1, $statistics['closed']);
    }

    /**
     * Test static query methods.
     */
    public function test_static_query_methods(): void
    {
        $user = User::factory()->create();
        $license = License::factory()->create();

        Ticket::factory()->create(['user_id' => $user->id, 'created_at' => now()->subDays(1)]);
        Ticket::factory()->create(['user_id' => $user->id, 'created_at' => now()->subDays(2)]);
        Ticket::factory()->create(['license_id' => $license->id, 'created_at' => now()->subDays(1)]);

        $userTickets = Ticket::getForUser($user->id);
        $this->assertCount(2, $userTickets);

        $licenseTickets = Ticket::getForLicense($license->id);
        $this->assertCount(1, $licenseTickets);

        $searchResults = Ticket::searchTickets('test');
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $searchResults);
    }

    /**
     * Test configuration validation.
     */
    public function test_configuration_validation(): void
    {
        $validTicket = Ticket::factory()->create([
            'subject' => 'Valid Subject',
            'content' => 'Valid content',
            'priority' => 'high',
            'status' => 'open',
        ]);

        $invalidTicket = Ticket::factory()->create([
            'subject' => '',
            'content' => '',
            'priority' => 'invalid',
            'status' => 'invalid',
        ]);

        $this->assertTrue($validTicket->isValidConfiguration());
        $this->assertEmpty($validTicket->validateConfiguration());

        $this->assertFalse($invalidTicket->isValidConfiguration());
        $errors = $invalidTicket->validateConfiguration();
        $this->assertContains('Ticket subject is required', $errors);
        $this->assertContains('Ticket content is required', $errors);
        $this->assertContains('Invalid priority value', $errors);
        $this->assertContains('Invalid status value', $errors);
    }

    /**
     * Test casts.
     */
    public function test_casts(): void
    {
        $ticket = Ticket::factory()->create([
            'user_id' => '1',
            'license_id' => '2',
            'invoice_id' => '3',
            'category_id' => '4',
        ]);

        $this->assertIsInt($ticket->user_id);
        $this->assertIsInt($ticket->license_id);
        $this->assertIsInt($ticket->invoice_id);
        $this->assertIsInt($ticket->category_id);
    }

    /**
     * Test boot method logging.
     */
    public function test_boot_method_logging(): void
    {
        $ticket = Ticket::factory()->create(['subject' => 'Test Subject']);

        $ticket->update(['subject' => 'Updated Subject']);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'Support ticket updated') &&
                   in_array('subject', $context['updated_fields']);
        });
    }
}
