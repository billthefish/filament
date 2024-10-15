<?php

namespace Filament\Tables\Columns;

use Filament\Support\Components\Contracts\HasEmbeddedView;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Js;
use Illuminate\View\ComponentAttributeBag;

class ColorColumn extends Column implements HasEmbeddedView
{
    use Concerns\CanBeCopied;
    use Concerns\CanWrap;

    public function toEmbeddedHtml(): string
    {
        $state = $this->getState();

        if ($state instanceof Collection) {
            $state = $state->all();
        }

        $attributes = $this->getExtraAttributeBag()
            ->class([
                'fi-ta-color',
                'fi-inline' => $this->isInline(),
            ]);

        if (empty($state)) {
            $placeholder = $this->getPlaceholder();

            ob_start(); ?>

            <div <?= $attributes->toHtml() ?>>
                <?php if (filled($placeholder !== null)) { ?>
                    <p class="fi-ta-placeholder">
                        <?= e($placeholder) ?>
                    </p>
                <?php } ?>
            </div>

            <?php return ob_get_clean();
        }

        $state = Arr::wrap($state);

        $alignment = $this->getAlignment();

        $attributes = $attributes
            ->class([
                'fi-wrapped' => $this->canWrap(),
                ($alignment instanceof Alignment) ? "fi-align-{$alignment->value}" : (is_string($alignment) ? $alignment : ''),
            ]);

        ob_start(); ?>

        <div <?= $attributes->toHtml() ?>>
            <?php foreach ($state as $stateItem) { ?>
                <?php
                    $isCopyable = $this->isCopyable($stateItem);

                if ($isCopyable) {
                    $copyableStateJs = Js::from($this->getCopyableState($stateItem) ?? $stateItem);
                    $copyMessageJs = Js::from($this->getCopyMessage($stateItem));
                    $copyMessageDurationJs = Js::from($this->getCopyMessageDuration($stateItem));
                }
                ?>

                <div <?= (new ComponentAttributeBag)
                    ->merge([
                        'x-on:click' => $isCopyable
                            ? <<<JS
                            window.navigator.clipboard.writeText({$copyableStateJs})
                            \$tooltip({$copyMessageJs}, {
                                theme: \$store.theme,
                                timeout: {$copyMessageDurationJs},
                            })
                            JS
                            : null,
                    ], escape: false)
                    ->class([
                        'fi-ta-color-item',
                        'fi-copyable' => $isCopyable,
                    ])
                    ->style([
                        'background-color: ' . e($stateItem) => $stateItem,
                    ])
                    ->toHtml() ?>></div>
            <?php } ?>
        </div>

        <?php return ob_get_clean();
    }
}
