<?php

use Illuminate\Http\Request;
use App\Rules\ClaudeValidation;
use App\Rules\ContentQuality;
use App\Rules\SpamDetection;
use App\Rules\PositiveSentiment;

/**
 * Example Laravel controller using AI-powered validation
 */
class ContactFormController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                new ClaudeValidation('realistic_name'),
            ],
            'email' => [
                'required',
                'email',
                new ClaudeValidation('professional_email'),
            ],
            'company' => [
                'required',
                'string',
                'max:255',
            ],
            'message' => [
                'required',
                'string',
                'min:50',
                new ClaudeValidation('appropriate_content'),
                new ContentQuality(minQualityScore: 7),
                new SpamDetection(threshold: 0.7),
                new PositiveSentiment(),
            ],
        ]);

        // Process valid submission
        return response()->json([
            'success' => true,
            'message' => 'Thank you for your submission!',
        ]);
    }
}

/**
 * Job Application Validation
 */
class JobApplicationController extends Controller
{
    public function submit(Request $request)
    {
        $validated = $request->validate([
            'cover_letter' => [
                'required',
                'string',
                'min:200',
                new ContentQuality(minQualityScore: 8),
                new ClaudeValidation(
                    'custom',
                    'Does this cover letter demonstrate genuine interest and relevant experience? Respond with valid or invalid.'
                ),
            ],
            'linkedin_url' => [
                'required',
                'url',
                new ClaudeValidation(
                    'custom',
                    'Is this a valid LinkedIn profile URL? {value}. Respond with valid or invalid.'
                ),
            ],
        ]);

        return redirect()->route('application.success');
    }
}

/**
 * Product Review Validation
 */
class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'review' => [
                'required',
                'string',
                'min:20',
                new SpamDetection(threshold: 0.6),
                new ContentQuality(minQualityScore: 6),
                new ClaudeValidation(
                    'custom',
                    'Is this a genuine product review (not spam or fake)? {value}. Respond with valid or invalid.'
                ),
            ],
            'rating' => 'required|integer|min:1|max:5',
        ]);

        // Store review
        return response()->json(['success' => true]);
    }
}
