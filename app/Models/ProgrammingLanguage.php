<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
class ProgrammingLanguage extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'is_active',
        'sort_order',
        'file_extension',
        'license_template',
    ];
    protected $casts = [
        'is_active' => 'boolean',
    ];
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'programming_language');
    }
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($language) {
            if (empty($language->slug)) {
                $language->slug = Str::slug($language->name);
            }
        });
        static::updating(function ($language) {
            if ($language->isDirty('name')) {
                $language->slug = Str::slug($language->name);
            }
        });
    }
    /**
     * Get the license template for this language.
     */
    public function getLicenseTemplate()
    {
        return $this->license_template ?: $this->getDefaultLicenseTemplate();
    }
    /**
     * Get default license template for this language.
     */
    private function getDefaultLicenseTemplate()
    {
        $templates = [
            'php' => "<?php\n/**\n * License Verification\n * Product: {PRODUCT_NAME}\n * Domain: {DOMAIN}\n".
                " * License: {LICENSE_CODE}\n * Valid Until: {VALID_UNTIL}\n */\n\n".
                "define('LICENSE_CODE', '{LICENSE_CODE}');\n".
                "define('LICENSE_DOMAIN', '{DOMAIN}');\n".
                "define('LICENSE_VALID_UNTIL', '{VALID_UNTIL}');\n\n".
                "define('PRODUCT_NAME', '{PRODUCT_NAME}');\n".
                "define('PRODUCT_VERSION', '{PRODUCT_VERSION}');\n\n".
                "// Verification function\n".
                "function verify_license() {\n".
                "    // License verification logic here\n".
                "    return true;\n".
                "}\n?>",
            'javascript' => "/**\n * License Verification\n * Product: {PRODUCT_NAME}\n * Domain: {DOMAIN}\n".
                " * License: {LICENSE_CODE}\n * Valid Until: {VALID_UNTIL}\n */\n\n".
                "const LICENSE_CONFIG = {\n".
                "    code: '{LICENSE_CODE}',\n".
                "    domain: '{DOMAIN}',\n".
                "    validUntil: '{VALID_UNTIL}',\n".
                "    product: '{PRODUCT_NAME}',\n".
                "    version: '{PRODUCT_VERSION}'\n".
                "};\n\n".
                "// Verification function\n".
                "function verifyLicense() {\n".
                "    // License verification logic here\n".
                "    return true;\n".
                "}\n\n".
                'module.exports = { LICENSE_CONFIG, verifyLicense };',
            'python' => "# License Verification\n# Product: {PRODUCT_NAME}\n# Domain: {DOMAIN}\n".
                "# License: {LICENSE_CODE}\n# Valid Until: {VALID_UNTIL}\n\n".
                "LICENSE_CODE = '{LICENSE_CODE}'\n".
                "LICENSE_DOMAIN = '{DOMAIN}'\n".
                "LICENSE_VALID_UNTIL = '{VALID_UNTIL}'\n\n".
                "PRODUCT_NAME = '{PRODUCT_NAME}'\n".
                "PRODUCT_VERSION = '{PRODUCT_VERSION}'\n\n".
                "# Verification function\n".
                "def verify_license():\n".
                "    # License verification logic here\n".
                '    return True',
            'csharp' => "// License Verification\n// Product: {PRODUCT_NAME}\n// Domain: {DOMAIN}\n".
                "// License: {LICENSE_CODE}\n// Valid Until: {VALID_UNTIL}\n\n".
                "using System;\n\n".
                "namespace LicenseVerification\n".
                "{\n".
                "    public static class LicenseConfig\n".
                "    {\n".
                "        public const string Code = \"{LICENSE_CODE}\";\n".
                "        public const string Domain = \"{DOMAIN}\";\n".
                "        public const string ValidUntil = \"{VALID_UNTIL}\";\n".
                "        public const string ProductName = \"{PRODUCT_NAME}\";\n".
                "        public const string ProductVersion = \"{PRODUCT_VERSION}\";\n\n".
                "        public static bool VerifyLicense()\n".
                "        {\n".
                "            // License verification logic here\n".
                "            return true;\n".
                "        }\n".
                "    }\n".
                '}',
            'java' => "// License Verification\n// Product: {PRODUCT_NAME}\n// Domain: {DOMAIN}\n".
                "// License: {LICENSE_CODE}\n// Valid Until: {VALID_UNTIL}\n\n".
                "package com.licenseverification;\n\n".
                "public class LicenseConfig {\n".
                "    public static final String CODE = \"{LICENSE_CODE}\";\n".
                "    public static final String DOMAIN = \"{DOMAIN}\";\n".
                "    public static final String VALID_UNTIL = \"{VALID_UNTIL}\";\n".
                "    public static final String PRODUCT_NAME = \"{PRODUCT_NAME}\";\n".
                "    public static final String PRODUCT_VERSION = \"{PRODUCT_VERSION}\";\n\n".
                "    public static boolean verifyLicense() {\n".
                "        // License verification logic here\n".
                "        return true;\n".
                "    }\n".
                '}',
            'cpp' => "// License Verification\n// Product: {PRODUCT_NAME}\n// Domain: {DOMAIN}\n".
                "// License: {LICENSE_CODE}\n// Valid Until: {VALID_UNTIL}\n\n".
                "#ifndef LICENSE_CONFIG_H\n".
                "#define LICENSE_CONFIG_H\n\n".
                "#include <string>\n\n".
                "namespace LicenseVerification {\n".
                "    const std::string CODE = \"{LICENSE_CODE}\";\n".
                "    const std::string DOMAIN = \"{DOMAIN}\";\n".
                "    const std::string VALID_UNTIL = \"{VALID_UNTIL}\";\n".
                "    const std::string PRODUCT_NAME = \"{PRODUCT_NAME}\";\n".
                "    const std::string PRODUCT_VERSION = \"{PRODUCT_VERSION}\";\n\n".
                "    bool verifyLicense();\n".
                "}\n\n".
                '#endif // LICENSE_CONFIG_H',
        ];
        return $templates[strtolower($this->slug)] ?? $templates['php'];
    }
    /**
     * Check if template file exists.
     */
    public function hasTemplateFile()
    {
        try {
            $templatePath = $this->getTemplateFilePath();
            return file_exists($templatePath);
        } catch (\Exception $e) {
            return false;
        }
    }
    /**
     * Get template file path.
     */
    public function getTemplateFilePath()
    {
        // Ensure slug is safe for filesystem paths (prevent directory traversal)
        $slug = (string)$this->slug;
        // Only allow alphanumeric, dashes and underscores.
        // If slug contains invalid chars, fallback to a sanitized slug.
        if (! preg_match('/^[a-z0-9\-_]+$/i', $slug)) {
            $slug = Str::slug($this->name ?: $slug);
        }
        return resource_path("templates/licenses/{$slug}.php");
    }
    /**
     * Get template information.
     */
    public function getTemplateInfo()
    {
        $templatePath = $this->getTemplateFilePath();
        return [
            'has_file' => $this->hasTemplateFile(),
            'file_path' => $templatePath,
            'file_size' => $this->hasTemplateFile() ? filesize($templatePath) : 0,
            'last_modified' => $this->hasTemplateFile()
                ? date('Y-m-d H:i:s', filemtime($templatePath))
                : null,
            'template_content' => $this->hasTemplateFile() ?
                file_get_contents($templatePath) : $this->getLicenseTemplate(),
        ];
    }
    /**
     * Get available template files from resources/templates/licenses/.
     */
    public static function getAvailableTemplateFiles()
    {
        $templateDir = resource_path('templates/licenses');
        $templates = [];
        if (is_dir($templateDir)) {
            // Look for both .php and .blade.php files
            $files = array_merge(
                glob($templateDir.'/*.php'),
                glob($templateDir.'/*.blade.php'),
            );
            foreach ($files as $file) {
                $filename = basename($file);
                // Remove both .php and .blade.php extensions
                $cleanName = str_replace(['.php', '.blade.php'], '', $filename);
                $templates[$cleanName] = [
                    'file_path' => $file,
                    'file_size' => filesize($file),
                    'last_modified' => date('Y-m-d H:i:s', filemtime($file)),
                ];
            }
        }
        return $templates;
    }
}
