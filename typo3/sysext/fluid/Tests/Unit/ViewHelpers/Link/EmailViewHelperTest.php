<?php
namespace TYPO3\CMS\Fluid\Tests\Unit\ViewHelpers\Link;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Fluid\ViewHelpers\Link\EmailViewHelper;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case
 */
class EmailViewHelperTest extends \TYPO3\CMS\Fluid\Tests\Unit\ViewHelpers\ViewHelperBaseTestcase
{
    /**
     * @var \TYPO3\CMS\Fluid\ViewHelpers\Link\EmailViewHelper
     */
    protected $viewHelper;

    /**
     * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
     */
    protected $cObjBackup;

    protected function setUp()
    {
        parent::setUp();
        $GLOBALS['TSFE'] = new \stdClass();
        $GLOBALS['TSFE']->cObj = $this->createMock(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);
        $this->viewHelper = $this->getMockBuilder($this->buildAccessibleProxy(\TYPO3\CMS\Fluid\ViewHelpers\Link\EmailViewHelper::class))
            ->setMethods(array('renderChildren'))
            ->getMock();
        $this->injectDependenciesIntoViewHelper($this->viewHelper);
        $this->viewHelper->initializeArguments();
    }

    /**
     * @test
     */
    public function renderCorrectlySetsTagNameAndAttributesAndContent()
    {
        $mockTagBuilder = $this->getMockBuilder(\TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder::class)
            ->setMethods(array('setTagName', 'addAttribute', 'setContent'))
            ->getMock();
        $mockTagBuilder->expects($this->once())->method('setTagName')->with('a');
        $mockTagBuilder->expects($this->once())->method('addAttribute')->with('href', 'mailto:some@email.tld');
        $mockTagBuilder->expects($this->once())->method('setContent')->with('some content');
        $this->viewHelper->_set('tag', $mockTagBuilder);
        $this->viewHelper->expects($this->any())->method('renderChildren')->will($this->returnValue('some content'));
        $this->viewHelper->initialize();
        $this->viewHelper->render('some@email.tld');
    }

    /**
     * @test
     */
    public function renderSetsTagContentToEmailIfRenderChildrenReturnNull()
    {
        $mockTagBuilder = $this->getMockBuilder(\TYPO3\CMS\Fluid\Core\ViewHelper\TagBuilder::class)
            ->setMethods(array('setTagName', 'addAttribute', 'setContent'))
            ->getMock();
        $mockTagBuilder->expects($this->once())->method('setContent')->with('some@email.tld');
        $this->viewHelper->_set('tag', $mockTagBuilder);
        $this->viewHelper->expects($this->any())->method('renderChildren')->will($this->returnValue(null));
        $this->viewHelper->initialize();
        $this->viewHelper->render('some@email.tld');
    }

    /**
     * @return array
     */
    public function renderEncodesEmailInFrontendDataProvider()
    {
        return [
            'Plain email' => [
                'some@email.tld',
                0,
                '<a href="mailto:some@email.tld">some@email.tld</a>',
            ],
            'Plain email with spam protection' => [
                'some@email.tld',
                1,
                '<a href="javascript:linkTo_UnCryptMailto(\'nbjmup+tpnfAfnbjm\/ume\');">some(at)email.tld</a>',
            ],
            'Plain email with ascii spam protection' => [
                'some@email.tld',
                'ascii',
                '<a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;&#115;&#111;&#109;&#101;&#64;&#101;&#109;&#97;&#105;&#108;&#46;&#116;&#108;&#100;">some(at)email.tld</a>',
            ],
            'Susceptible email' => [
                '"><script>alert(\'email\')</script>',
                0,
                '<a href="mailto:&quot;&gt;&lt;script&gt;alert(\'email\')&lt;/script&gt;">&quot;&gt;&lt;script&gt;alert(\'email\')&lt;/script&gt;</a>',
            ],
            'Susceptible email with spam protection' => [
                '"><script>alert(\'email\')</script>',
                1,
                '<a href="javascript:linkTo_UnCryptMailto(\'nbjmup+\u0022\u003E\u003Ctdsjqu\u003Ebmfsu(\u0027fnbjm\u0027)\u003C0tdsjqu\u003E\');">&quot;&gt;&lt;script&gt;alert(\'email\')&lt;/script&gt;</a>',
            ],
            'Susceptible email with ascii spam protection' => [
                '"><script>alert(\'email\')</script>',
                'ascii',
                '<a href="&#109;&#97;&#105;&#108;&#116;&#111;&#58;&#34;&#62;&#60;&#115;&#99;&#114;&#105;&#112;&#116;&#62;&#97;&#108;&#101;&#114;&#116;&#40;&#39;&#101;&#109;&#97;&#105;&#108;&#39;&#41;&#60;&#47;&#115;&#99;&#114;&#105;&#112;&#116;&#62;">&quot;&gt;&lt;script&gt;alert(\'email\')&lt;/script&gt;</a>',
            ],
        ];
    }

    /**
     * @test
     * @dataProvider renderEncodesEmailInFrontendDataProvider
     * @param string $email
     * @param string $spamProtectEmailAddresses
     * @param string $expected
     */
    public function renderEncodesEmailInFrontend($email, $spamProtectEmailAddresses, $expected)
    {
        /** @var TypoScriptFrontendController $tsfe */
        $tsfe = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->setMethods(['dummy'])
            ->disableOriginalConstructor()
            ->getMock();
        $tsfe->cObj = new ContentObjectRenderer();
        $tsfe->spamProtectEmailAddresses = $spamProtectEmailAddresses;
        $tsfe->config = [
            'config' => [
                'spamProtectEmailAddresses_atSubst' => '',
                'spamProtectEmailAddresses_lastDotSubst' => '',
            ],
        ];
        $GLOBALS['TSFE'] = $tsfe;
        $viewHelper = $this->getMockBuilder(EmailViewHelper::class)
            ->setMethods(array('isFrontendAvailable', 'renderChildren'))
            ->getMock();
        $viewHelper->expects($this->once())->method('isFrontendAvailable')->willReturn(true);
        $viewHelper->expects($this->once())->method('renderChildren')->willReturn(null);
        $viewHelper->initialize();
        $this->assertSame(
            $expected,
            $viewHelper->render($email)
        );
    }
}
