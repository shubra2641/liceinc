<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\Product;
use App\Models\ProgrammingLanguage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

/**
 * Test suite for ProgrammingLanguageController.
 *
 * Tests all programming language management functionality including:
 * - CRUD operations for programming languages
 * - Template file management and validation
 * - File upload and creation operations
 * - Export functionality
 * - Error handling and logging
 */
class ProgrammingLanguageControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected User $admin;

    protected User $customer;

    protected ProgrammingLanguage $programmingLanguage;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com',
        ]);

        $this->customer = User::factory()->create([
            'role' => 'customer',
            'email' => 'customer@test.com',
        ]);

        // Create test programming language
        $this->programmingLanguage = ProgrammingLanguage::factory()->create([
            'name' => 'PHP',
            'slug' => 'php',
            'description' => 'PHP programming language',
            'icon' => 'fab fa-php',
            'is_active' => true,
            'sort_order' => 1,
            'file_extension' => 'php',
        ]);

        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test admin can access programming languages index.
     */
    public function test_admin_can_access_programming_languages_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.programming-languages.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.programming-languages.index');
        $response->assertViewHas(['programmingLanguages', 'languages', 'availableTemplates']);
    }

    /**
     * Test customer cannot access programming languages index.
     */
    public function test_customer_cannot_access_programming_languages_index(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('admin.programming-languages.index'));

        $response->assertStatus(403);
    }

    /**
     * Test admin can access create form.
     */
    public function test_admin_can_access_create_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.programming-languages.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.programming-languages.create');
    }

    /**
     * Test admin can create programming language with valid data.
     */
    public function test_admin_can_create_programming_language_with_valid_data(): void
    {
        $languageData = [
            'name' => 'JavaScript',
            'slug' => 'javascript',
            'description' => 'JavaScript programming language',
            'icon' => 'fab fa-js',
            'is_active' => true,
            'sort_order' => 2,
            'file_extension' => 'js',
            'license_template' => 'JavaScript License Template',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.programming-languages.store'), $languageData);

        $response->assertRedirect(route('admin.programming-languages.index'));
        $response->assertSessionHas('success', 'Programming language created successfully.');

        $this->assertDatabaseHas('programming_languages', [
            'name' => 'JavaScript',
            'slug' => 'javascript',
            'description' => 'JavaScript programming language',
            'icon' => 'fab fa-js',
            'is_active' => true,
            'sort_order' => 2,
            'file_extension' => 'js',
        ]);
    }

    /**
     * Test programming language creation fails with invalid data.
     */
    public function test_programming_language_creation_fails_with_invalid_data(): void
    {
        $invalidData = [
            'name' => '', // Required field missing
            'slug' => 'php', // Already exists
            'description' => str_repeat('a', 1001), // Too long
            'icon' => str_repeat('a', 101), // Too long
            'sort_order' => -1, // Too small
            'file_extension' => str_repeat('a', 11), // Too long
            'license_template' => str_repeat('a', 50001), // Too long
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.programming-languages.store'), $invalidData);

        $response->assertSessionHasErrors([
            'name', 'slug', 'description', 'icon', 'sort_order', 'file_extension', 'license_template',
        ]);
    }

    /**
     * Test admin can view programming language details.
     */
    public function test_admin_can_view_programming_language_details(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.programming-languages.show', $this->programmingLanguage));

        $response->assertStatus(200);
        $response->assertViewIs('admin.programming-languages.show');
        $response->assertViewHas(['programmingLanguage', 'availableTemplates']);
    }

    /**
     * Test admin can access edit form.
     */
    public function test_admin_can_access_edit_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.programming-languages.edit', $this->programmingLanguage));

        $response->assertStatus(200);
        $response->assertViewIs('admin.programming-languages.edit');
        $response->assertViewHas('programmingLanguage');
    }

    /**
     * Test admin can update programming language with valid data.
     */
    public function test_admin_can_update_programming_language_with_valid_data(): void
    {
        $updateData = [
            'name' => 'Updated PHP',
            'slug' => 'updated-php',
            'description' => 'Updated PHP programming language',
            'icon' => 'fab fa-php-square',
            'is_active' => false,
            'sort_order' => 5,
            'file_extension' => 'php8',
            'license_template' => 'Updated PHP License Template',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.programming-languages.update', $this->programmingLanguage), $updateData);

        $response->assertRedirect(route('admin.programming-languages.index'));
        $response->assertSessionHas('success', 'Programming language updated successfully.');

        $this->assertDatabaseHas('programming_languages', [
            'id' => $this->programmingLanguage->id,
            'name' => 'Updated PHP',
            'slug' => 'updated-php',
            'description' => 'Updated PHP programming language',
            'icon' => 'fab fa-php-square',
            'is_active' => false,
            'sort_order' => 5,
            'file_extension' => 'php8',
        ]);
    }

    /**
     * Test programming language update fails with invalid data.
     */
    public function test_programming_language_update_fails_with_invalid_data(): void
    {
        $invalidData = [
            'name' => '', // Required field missing
            'slug' => 'php', // Same as existing
            'description' => str_repeat('a', 1001), // Too long
            'icon' => str_repeat('a', 101), // Too long
            'sort_order' => -1, // Too small
            'file_extension' => str_repeat('a', 11), // Too long
            'license_template' => str_repeat('a', 50001), // Too long
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.programming-languages.update', $this->programmingLanguage), $invalidData);

        $response->assertSessionHasErrors([
            'name', 'description', 'icon', 'sort_order', 'file_extension', 'license_template',
        ]);
    }

    /**
     * Test admin can delete programming language without products.
     */
    public function test_admin_can_delete_programming_language_without_products(): void
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.programming-languages.destroy', $this->programmingLanguage));

        $response->assertRedirect(route('admin.programming-languages.index'));
        $response->assertSessionHas('success', 'Programming language deleted successfully.');

        $this->assertDatabaseMissing('programming_languages', [
            'id' => $this->programmingLanguage->id,
        ]);
    }

    /**
     * Test admin cannot delete programming language with products.
     */
    public function test_admin_cannot_delete_programming_language_with_products(): void
    {
        // Create a product that uses this programming language
        Product::factory()->create([
            'programming_language_id' => $this->programmingLanguage->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.programming-languages.destroy', $this->programmingLanguage));

        $response->assertRedirect(route('admin.programming-languages.index'));
        $response->assertSessionHas('error', 'Cannot delete programming language that is being used by products.');

        $this->assertDatabaseHas('programming_languages', [
            'id' => $this->programmingLanguage->id,
        ]);
    }

    /**
     * Test admin can toggle programming language status.
     */
    public function test_admin_can_toggle_programming_language_status(): void
    {
        $initialStatus = $this->programmingLanguage->is_active;

        $response = $this->actingAs($this->admin)
            ->post(route('admin.programming-languages.toggle', $this->programmingLanguage));

        $response->assertRedirect(route('admin.programming-languages.index'));

        $expectedStatus = $initialStatus ? 'deactivated' : 'activated';
        $response->assertSessionHas('success', "Programming language {$expectedStatus} successfully.");

        $this->programmingLanguage->refresh();
        $this->assertNotEquals($initialStatus, $this->programmingLanguage->is_active);
    }

    /**
     * Test admin can get license file content.
     */
    public function test_admin_can_get_license_file_content(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.programming-languages.license-file-content', $this->programmingLanguage->slug), [
                'type' => 'default',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'content',
            'language',
            'type',
        ]);
    }

    /**
     * Test admin can get template info.
     */
    public function test_admin_can_get_template_info(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.programming-languages.template-info', $this->programmingLanguage));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'exists',
            'path',
            'size',
            'last_modified',
        ]);
    }

    /**
     * Test admin can get available templates.
     */
    public function test_admin_can_get_available_templates(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.programming-languages.available-templates'));

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    /**
     * Test admin can validate templates.
     */
    public function test_admin_can_validate_templates(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.programming-languages.validate-templates'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'validation_results',
            'summary' => [
                'total_templates',
                'valid_templates',
                'templates_with_errors',
                'templates_with_warnings',
            ],
        ]);
    }

    /**
     * Test admin can upload template file.
     */
    public function test_admin_can_upload_template_file(): void
    {
        $templateFile = UploadedFile::fake()->create('template.php', 100, 'php');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.programming-languages.upload-template', $this->programmingLanguage), [
                'template_file' => $templateFile,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Template file uploaded successfully',
        ]);
    }

    /**
     * Test template file upload fails with invalid file.
     */
    public function test_template_file_upload_fails_with_invalid_file(): void
    {
        $invalidFile = UploadedFile::fake()->create('template.txt', 100, 'txt');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.programming-languages.upload-template', $this->programmingLanguage), [
                'template_file' => $invalidFile,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['template_file']);
    }

    /**
     * Test admin can create template file from content.
     */
    public function test_admin_can_create_template_file_from_content(): void
    {
        $templateContent = '<?php echo "Test License Template"; ?>';

        $response = $this->actingAs($this->admin)
            ->post(route('admin.programming-languages.create-template', $this->programmingLanguage), [
                'template_content' => $templateContent,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Template file created successfully',
        ]);
    }

    /**
     * Test template creation fails with invalid content.
     */
    public function test_template_creation_fails_with_invalid_content(): void
    {
        $invalidContent = 'a'; // Too short

        $response = $this->actingAs($this->admin)
            ->post(route('admin.programming-languages.create-template', $this->programmingLanguage), [
                'template_content' => $invalidContent,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['template_content']);
    }

    /**
     * Test admin can export programming languages to CSV.
     */
    public function test_admin_can_export_programming_languages_to_csv(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.programming-languages.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition');
    }

    /**
     * Test admin can get template content.
     */
    public function test_admin_can_get_template_content(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.programming-languages.template-content', $this->programmingLanguage));

        $response->assertStatus(404); // Template file doesn't exist
        $response->assertJson([
            'success' => false,
            'message' => 'Template file not found',
        ]);
    }

    /**
     * Test unauthorized access attempts.
     */
    public function test_unauthorized_access_returns_403(): void
    {
        $routes = [
            'admin.programming-languages.index',
            'admin.programming-languages.create',
            'admin.programming-languages.store',
            'admin.programming-languages.show',
            'admin.programming-languages.edit',
            'admin.programming-languages.update',
            'admin.programming-languages.destroy',
            'admin.programming-languages.toggle',
            'admin.programming-languages.export',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->customer)
                ->get(route($route, $this->programmingLanguage));

            $response->assertStatus(403);
        }
    }

    /**
     * Test guest access attempts.
     */
    public function test_guest_access_redirects_to_login(): void
    {
        $response = $this->get(route('admin.programming-languages.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test database transaction rollback on error.
     */
    public function test_database_transaction_rollback_on_error(): void
    {
        // Mock database error
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        // This should trigger an error and rollback
        $response = $this->actingAs($this->admin)
            ->post(route('admin.programming-languages.store'), [
                'name' => 'Test Language',
            ]);

        // Should handle error gracefully
        $response->assertRedirect();
    }

    /**
     * Test validation error messages.
     */
    public function test_validation_error_messages(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.programming-languages.store'), [
                'name' => '',
                'slug' => 'php', // Already exists
                'sort_order' => -1,
            ]);

        $response->assertSessionHasErrors(['name', 'slug', 'sort_order']);

        $errors = $response->session()->get('errors')->getBag('default');
        $this->assertTrue($errors->has('name'));
        $this->assertTrue($errors->has('slug'));
        $this->assertTrue($errors->has('sort_order'));
    }

    /**
     * Test boolean field handling.
     */
    public function test_boolean_field_handling(): void
    {
        $languageData = [
            'name' => 'Test Language',
            'is_active' => '1', // String boolean
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.programming-languages.store'), $languageData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Programming language created successfully.');

        $this->assertDatabaseHas('programming_languages', [
            'name' => 'Test Language',
            'is_active' => true,
        ]);
    }

    /**
     * Test slug auto-generation.
     */
    public function test_slug_auto_generation(): void
    {
        $languageData = [
            'name' => 'Test Language',
            'slug' => '', // Empty slug
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.programming-languages.store'), $languageData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Programming language created successfully.');

        $this->assertDatabaseHas('programming_languages', [
            'name' => 'Test Language',
            'slug' => 'test-language',
        ]);
    }

    /**
     * Test integer field handling.
     */
    public function test_integer_field_handling(): void
    {
        $languageData = [
            'name' => 'Test Language',
            'sort_order' => '5', // String integer
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.programming-languages.store'), $languageData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Programming language created successfully.');

        $this->assertDatabaseHas('programming_languages', [
            'name' => 'Test Language',
            'sort_order' => 5,
        ]);
    }

    /**
     * Test string field trimming.
     */
    public function test_string_field_trimming(): void
    {
        $languageData = [
            'name' => '  Test Language  ',
            'description' => '  Test Description  ',
            'icon' => '  fab fa-test  ',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.programming-languages.store'), $languageData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Programming language created successfully.');

        $this->assertDatabaseHas('programming_languages', [
            'name' => 'Test Language',
            'description' => 'Test Description',
            'icon' => 'fab fa-test',
        ]);
    }
}
