<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ArtisanRunner\Streaming;

use Illuminate\Support\Str;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Emits SSE prompt events and waits for UI answers via {@see InteractiveQuestionBroker}.
 *
 * Extends SymfonyQuestionHelper so it can be assigned to SymfonyStyle::$questionHelper
 * (the path used by Laravel $this->confirm() / ask() / choice()).
 *
 * @phpstan-type EmitCallable callable(string $event, array<string, mixed> $payload): void
 */
final class BridgedQuestionHelper extends SymfonyQuestionHelper
{
    /** @var EmitCallable */
    private $emit;

    /**
     * @param  EmitCallable  $emit
     */
    public function __construct(
        private readonly string $runId,
        private readonly InteractiveQuestionBroker $broker,
        callable $emit,
    ) {
        $this->emit = $emit;
    }

    public function ask(InputInterface $input, OutputInterface $output, Question $question): mixed
    {
        $promptId = (string) Str::uuid();
        $type = $this->resolveType($question);

        $payload = [
            'id' => $promptId,
            'type' => $type,
            'question' => $question->getQuestion(),
            'default' => $question->getDefault(),
        ];

        if ($question instanceof ChoiceQuestion) {
            $payload['choices'] = array_values($question->getChoices());
            $payload['multiselect'] = $question->isMultiselect();
        }

        ($this->emit)('prompt', $payload);

        $answer = $this->broker->waitForAnswer($this->runId, $promptId);

        if ($question instanceof ConfirmationQuestion) {
            return filter_var($answer, FILTER_VALIDATE_BOOLEAN);
        }

        return $answer;
    }

    private function resolveType(Question $question): string
    {
        if ($question instanceof ConfirmationQuestion) {
            return 'confirm';
        }

        if ($question instanceof ChoiceQuestion) {
            return 'choice';
        }

        return 'ask';
    }
}
