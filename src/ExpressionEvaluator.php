<?php

declare(strict_types=1);

namespace OriolSegura\Decimal;

use OriolSegura\Decimal\Exceptions\InvalidExpressionException;
use OriolSegura\Decimal\Exceptions\UnknownMathematicalOperatorException;
use OriolSegura\Decimal\Exceptions\WrongDecimalFormatException;

final readonly class ExpressionEvaluator
{
    private const PRECEDENCE = [
        '+' => 1,
        '-' => 1,
        '*' => 2,
        '/' => 2,
    ];

    /**
     * @throws InvalidExpressionException
     * @throws UnknownMathematicalOperatorException
     * @throws WrongDecimalFormatException
     */
    public function evaluate(string $expression): Decimal
    {
        $tokens = $this->tokenize($expression);
        $rpn    = $this->toReversePolishNotation($tokens);

        return $this->evaluateRpn($rpn);
    }

    private function tokenize(string $expression): array
    {
        $expression = str_replace(' ', '', $expression);

        $tokens       = [];
        $length       = strlen($expression);
        $numberBuffer = '';

        for ($i = 0; $i < $length; $i++) {
            $char = $expression[$i];

            if (is_numeric($char) || $char === '.') {
                $numberBuffer .= $char;
            } else {
                if ($char === '-') {
                    $prevChar = $i > 0 ? $expression[$i - 1] : null;
                    if ($prevChar === null || in_array($prevChar, ['+', '-', '*', '/', '('], true)) {
                        $numberBuffer .= $char;

                        continue;
                    }
                }

                if ($numberBuffer !== '') {
                    $tokens[]     = $numberBuffer;
                    $numberBuffer = '';
                }

                $tokens[] = $char;
            }
        }

        if ($numberBuffer !== '') {
            $tokens[] = $numberBuffer;
        }

        return $tokens;
    }

    /**
     * @throws InvalidExpressionException
     * @throws UnknownMathematicalOperatorException
     */
    private function toReversePolishNotation(array $tokens): array
    {
        $output    = [];
        $operators = [];

        foreach ($tokens as $token) {
            if (is_numeric($token)) {
                $output[] = $token;
            } elseif ($token === '(') {
                $operators[] = $token;
            } elseif ($token === ')') {
                while (count($operators) > 0 && end($operators) !== '(') {
                    $output[] = array_pop($operators);
                }
                if (count($operators) === 0 || end($operators) !== '(') {
                    throw new InvalidExpressionException;
                }
                array_pop($operators);
            } elseif (isset(self::PRECEDENCE[$token])) {
                while (
                    count($operators) > 0
                    && end($operators) !== '('
                    && self::PRECEDENCE[end($operators)] >= self::PRECEDENCE[$token]
                ) {
                    $output[] = array_pop($operators);
                }
                $operators[] = $token;
            } else {
                throw new UnknownMathematicalOperatorException($token);
            }
        }

        while (count($operators) > 0) {
            $op = array_pop($operators);
            if ($op === '(' || $op === ')') {
                throw new InvalidExpressionException;
            }
            $output[] = $op;
        }

        return $output;
    }

    /**
     * @throws InvalidExpressionException
     * @throws UnknownMathematicalOperatorException
     * @throws WrongDecimalFormatException
     */
    private function evaluateRpn(array $rpnTokens): Decimal
    {
        $stack = [];

        foreach ($rpnTokens as $token) {
            if (is_numeric($token)) {
                $stack[] = Decimal::from($token);
            } else {
                if (count($stack) < 2) {
                    throw new InvalidExpressionException;
                }

                $right = array_pop($stack);
                $left  = array_pop($stack);

                $stack[] = match ($token) {
                    '+'     => $left->plus($right),
                    '-'     => $left->minus($right),
                    '*'     => $left->mul($right),
                    '/'     => $left->div($right),
                    default => throw new UnknownMathematicalOperatorException($token),
                };
            }
        }

        if (count($stack) !== 1) {
            throw new InvalidExpressionException;
        }

        return $stack[0];
    }
}
