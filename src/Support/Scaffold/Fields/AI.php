<?php

namespace Tir\Crud\Support\Scaffold\Fields;

use Tir\Crud\Support\Enums\FilterType;

/**
 * AI Field - A wrapper field that adds AI assistance to text inputs
 *
 * This field wraps Text or Textarea inputs and injects AI capabilities,
 * allowing users to translate, improve, summarize, or expand text content.
 *
 * @example Basic usage with textarea (default)
 * AI::make('description')
 *
 * @example With text input
 * AI::make('title')->inputType('text')
 *
 * @example With textarea and custom prompt
 * AI::make('description')
 *     ->inputType('textarea')
 *     ->prompt('This is a job description for IT positions')
 *     ->aiActions(['translate_en', 'translate_de', 'improve_grammar'])
 *
 * @example Full example with all options
 * AI::make('cover_letter')
 *     ->inputType('textarea')
 *     ->rows(10)
 *     ->prompt('This is a cover letter for job applications. Keep it professional.')
 *     ->aiActions(['improve_grammar', 'translate_en', 'translate_de', 'expand'])
 *     ->display('Cover Letter')
 *     ->rules('required', 'min:100')
 *     ->col(24)
 */
class AI extends BaseField
{
    protected string $type = 'AI';

    /**
     * The underlying input type: 'text' or 'textarea'
     */
    protected string $inputType = 'textarea';

    /**
     * Number of rows for textarea (only applies when inputType is 'textarea')
     */
    protected int $rows = 4;

    /**
     * System prompt to provide context to the AI about this field
     */
    protected string $aiPrompt = '';

    /**
     * Available AI actions for this field
     *
     * Default actions:
     * - translate_en: Translate to English
     * - translate_de: Translate to German
     * - translate_fa: Translate to Persian
     * - improve_grammar: Fix grammar and improve clarity
     * - summarize: Make text shorter
     * - expand: Make text longer/more detailed
     */
    protected array $aiActions = [
        'translate_en',
        'translate_de',
        'translate_fa',
        'improve_grammar',
        'summarize',
        'expand',
    ];

    /**
     * Component-specific options passed to the dynamic component
     * Use this to pass props that are specific to the inputType component
     */
    protected array $inputOptions = [];

    /**
     * API endpoint URL for AI text assist
     * Default: '/ai/text-assist'
     */
    protected string $aiEndpoint = '/ai/text-assist';

    /**
     * Locale for UI translations (en, de)
     * Default: uses app()->getLocale()
     */
    protected string $locale = 'en';

    protected FilterType|string $filterType = FilterType::Search;

    /**
     * Initialize default values
     */
    protected function init(): void
    {
        $this->locale = app()->getLocale();
    }

    /**
     * Set the underlying input type
     *
     * @param string $type - 'text' or 'textarea'
     * @return static
     *
     * @example
     * AI::make('title')->inputType('text')
     * AI::make('description')->inputType('textarea')
     */
    public function inputType(string $type): static
    {
        $this->inputType = $type;
        return $this;
    }

    /**
     * Set number of rows for textarea
     *
     * Only applies when inputType is 'textarea'.
     *
     * @param int $count - Number of rows (default: 4)
     * @return static
     *
     * @example
     * AI::make('description')->inputType('textarea')->rows(6)
     */
    public function rows(int $count): static
    {
        $this->rows = $count;
        return $this;
    }

    /**
     * Set the AI system prompt for this field
     *
     * The prompt provides context to the AI about what this field contains,
     * helping it generate more accurate and relevant responses.
     *
     * @param string $prompt - System prompt describing the field context
     * @return static
     *
     * @example
     * AI::make('job_description')
     *     ->inputType('textarea')
     *     ->prompt('This field contains IT job descriptions. Keep technical terms accurate.')
     *
     * @example
     * AI::make('email_body')
     *     ->prompt('Professional business email. Maintain formal tone.')
     */
    public function prompt(string $prompt): static
    {
        $this->aiPrompt = $prompt;
        return $this;
    }

    /**
     * Set available AI actions for this field
     *
     * Use this to limit which AI actions are available for this specific field.
     * If not called, all default actions will be available.
     *
     * @param array $actions - Array of action identifiers
     * @return static
     *
     * Available actions:
     * - 'translate_en' - Translate to English
     * - 'translate_de' - Translate to German
     * - 'translate_fa' - Translate to Persian
     * - 'improve_grammar' - Fix grammar and improve clarity
     * - 'summarize' - Make text shorter
     * - 'expand' - Make text longer/more detailed
     *
     * Note: Custom prompt input is always available regardless of this setting.
     *
     * @example Only translation actions
     * AI::make('content')
     *     ->aiActions(['translate_en', 'translate_de', 'translate_fa'])
     *
     * @example Writing assistance only
     * AI::make('essay')
     *     ->aiActions(['improve_grammar', 'summarize', 'expand'])
     */
    public function aiActions(array $actions): static
    {
        $this->aiActions = $actions;
        return $this;
    }

    /**
     * Set component-specific options for the dynamic component
     *
     * Use this to pass props that are specific to the inputType component.
     * These options will be spread as props to the underlying component.
     *
     * @param array $options - Array of options to pass to the component
     * @return static
     *
     * @example Using with CustomQuestions component
     * AI::make('motivation_text')
     *     ->inputType('CustomQuestions')
     *     ->inputOptions(['questions' => $questions])
     *     ->prompt('Motivation letter content')
     *
     * @example Using with Mentions component
     * AI::make('content')
     *     ->inputType('Mentions')
     *     ->inputOptions(['prefix' => '@', 'options' => $users])
     */
    public function inputOptions(array $options): static
    {
        $this->inputOptions = $options;
        return $this;
    }

    /**
     * Set a custom API endpoint for AI text assist
     *
     * Override the default endpoint '/ai/text-assist' with a custom one.
     * Useful when you need different AI processing for specific fields.
     *
     * @param string $endpoint - The API endpoint URL
     * @return static
     *
     * @example Using a custom endpoint
     * AI::make('legal_text')
     *     ->endpoint('/ai/legal-assist')
     *     ->prompt('Legal document. Use formal language.')
     */
    public function endpoint(string $endpoint): static
    {
        $this->aiEndpoint = $endpoint;
        return $this;
    }

    /**
     * Set the locale for UI translations
     *
     * @param string $locale - The locale code (en, de)
     * @return static
     *
     * @example Using German locale
     * AI::make('description')
     *     ->locale('de')
     */
    public function locale(string $locale): static
    {
        $this->locale = $locale;
        return $this;
    }
}
