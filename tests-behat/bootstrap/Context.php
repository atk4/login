<?php

declare(strict_types=1);

namespace Atk4\Login\Behat;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Hook\Scope\BeforeStepScope;
use Behat\MinkExtension\Context\MinkContext;
use Exception;

class Context extends MinkContext
{
    /** @var null Temporary store button id when press. Used in js callback test. */
    protected $buttonId;

    /**
     * @When I press button :arg1
     */
    public function iPressButton($arg1)
    {
        $this->getSession()->wait(5000,
        "$('*[text=\"" . $arg1 . "\"').children().length > 0"
        );

        $button = $this->getSession()->getPage()->find('xpath', '//div[text()="' . $arg1 . '"]');
        // store button id.
        $this->buttonId = $button->getAttribute('id');
        // fix "is out of bounds of viewport width and height" for Firefox
        $button->focus();
        $button->click();
    }

    /**
     * @Then I see button :arg1
     */
    public function iSeeButton($arg1)
    {
        // var_dump($this->getSession()->getPage()->getHtml()); // WebDriver\Exception\NoSuchElement: Element not found with xpath, //html

        $this->getSession()->wait(5000,
            "$('*[text=\"" . $arg1 . "\"').children().length > 0"
        );

        $element = $this->getSession()->getPage()->find('xpath', '//div[text()="' . $arg1 . '"]');
        if ($element->getAttribute('style')) {
            throw new Exception("Element with text \"{$arg1}\" must be invisible");
        }
    }

    /**
     * @Then dump :arg1
     */
    public function dump($arg1)
    {
        $element = $this->getSession()->getPage()->find('xpath', '//div[text()="' . $arg1 . '"]');
        var_dump($element->getOuterHtml());
    }

    /**
     * @Then I don't see button :arg1
     */
    public function iDontSeeButton($arg1)
    {
        $element = $this->getSession()->getPage()->find('xpath', '//div[text()="' . $arg1 . '"]');
        if (strpos('display: none', $element->getAttribute('style')) !== false) {
            throw new Exception("Element with text \"{$arg1}\" must be invisible");
        }
    }

    /**
     * @Then Modal opens with text :arg1
     *
     * Check if text is present in modal or dynamic modal.
     */
    public function modalOpensWithText($arg1)
    {
        // get modal
        $modal = $this->getSession()->getPage()->find('css', '.modal.transition.visible.active.front');
        if ($modal === null) {
            throw new Exception('No modal found');
        }
        // find text in modal
        $text = $modal->find('xpath', '//div[text()="' . $arg1 . '"]');
        if (!$text || $text->getText() !== $arg1) {
            throw new Exception('No such text in modal');
        }
    }

    /**
     * @BeforeStep
     */
    public function closeAllToasts(BeforeStepScope $event): void
    {
        if (!$this->getSession()->getDriver()->isStarted()) {
            return;
        }

        if (strpos($event->getStep()->getText(), 'Toast display should contains text ') !== 0) {
            $this->getSession()->executeScript('$(\'.toast-box > .ui.toast\').toast(\'close\');');
        }
    }

    /**
     * @AfterStep
     */
    public function waitUntilLoadingAndAnimationFinished(AfterStepScope $event): void
    {
        $this->jqueryWait();
        $this->disableAnimations();
        $this->assertNoException();
        $this->disableDebounce();
    }

    protected function disableAnimations(): void
    {
        // disable all CSS/jQuery animations/transitions
        $toCssFx = function ($selector, $cssPairs) {
            $css = [];
            foreach ($cssPairs as $k => $v) {
                foreach ([$k, '-moz-' . $k, '-webkit-' . $k] as $k2) {
                    $css[] = $k2 . ': ' . $v . ' !important;';
                }
            }

            return $selector . ' { ' . implode(' ', $css) . ' }';
        };

        $durationAnimation = 0.005;
        $durationToast = 5;
        $css = $toCssFx('*', [
            'animation-delay' => $durationAnimation . 's',
            'animation-duration' => $durationAnimation . 's',
            'transition-delay' => $durationAnimation . 's',
            'transition-duration' => $durationAnimation . 's',
        ]) . $toCssFx('.ui.toast-container .toast-box .progressing.wait', [
            'animation-duration' => $durationToast . 's',
            'transition-duration' => $durationToast . 's',
        ]);

        $this->getSession()->executeScript(
            'if (Array.prototype.filter.call(document.getElementsByTagName("style"), e => e.getAttribute("about") === "atk-test-behat").length === 0) {'
            . ' $(\'<style about="atk-test-behat">' . $css . '</style>\').appendTo(\'head\');'
            . ' }'
            . 'jQuery.fx.off = true;'
        );
    }

    protected function assertNoException(): void
    {
        foreach ($this->getSession()->getPage()->findAll('css', 'div.ui.negative.icon.message > div.content > div.header') as $elem) {
            if ($elem->getText() === 'Critical Error') {
                throw new Exception('Page contains uncaught exception');
            }
        }
    }

    protected function disableDebounce(): void
    {
        $this->getSession()->executeScript('atk.options.set("debounceTimeout", 20)');
    }

    protected function getFinishedScript(): string
    {
        return 'document.readyState === \'complete\''
            . ' && typeof jQuery !== \'undefined\' && jQuery.active === 0'
            . ' && typeof atk !== \'undefined\' && atk.vueService.areComponentsLoaded()';
    }

    /**
     * Sleep for a certain time in ms.
     *
     * @Then I wait :arg1 ms
     */
    public function iWait($arg1)
    {
        $this->getSession()->wait($arg1);
    }

    /**
     * Wait till jQuery AJAX request finished and no animation is perform.
     */
    protected function jqueryWait(string $extraWaitCondition = 'true', $maxWaitdurationMs = 5000)
    {
        $finishedScript = '(' . $this->getFinishedScript() . ') && (' . $extraWaitCondition . ')';

        $s = microtime(true);
        $c = 0;
        while (microtime(true) - $s <= $maxWaitdurationMs / 1000) {
            $this->getSession()->wait($maxWaitdurationMs, $finishedScript);
            usleep(10000);
            if ($this->getSession()->evaluateScript($finishedScript)) {
                if (++$c >= 2) {
                    return;
                }
            } else {
                $c = 0;
                usleep(50000);
            }
        }

        throw new Exception('jQuery did not finished within a time limit');
    }
}
