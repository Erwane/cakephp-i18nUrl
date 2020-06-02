<?php
declare(strict_types=1);

namespace I18nUrl\Middleware;

use Cake\Core\Configure;
use Cake\Database\Type;
use Cake\I18n\Date as CakeDate;
use Cake\I18n\FrozenDate;
use Cake\I18n\I18n;
use Cake\I18n\Time;
use Cake\I18n\FrozenTime;
use Cake\Http\Exception\NotFoundException;
use Cake\Routing\Exception\MissingRouteException;
use I18nUrl\Routing\Router;
use Locale;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LocaleMiddleware implements MiddlewareInterface
{
    private $_loop = 0;

    protected $_locales = ['fr' => 'fr_FR', 'en' => 'en_GB'];

    protected static $_formats = [
        'fr_FR' => [
            'date' => 'dd MMM YYYY',
            'time' => 'HH:mm',
            'datetime' => 'dd MMM YYYY HH:mm',
            'timezone' => 'Europe/Paris',
        ],
        'en_GB' => [
            'date' => 'dd MMM YYYY',
            'time' => 'HH:mm',
            'datetime' => 'dd MMM YYYY HH:mm',
            'timezone' => 'Europe/London',
        ],
    ];

    /**
     * Invoke method.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request The request.
     * @param \Psr\Http\Message\ResponseInterface $response The response.
     * @param callable $next Callback to invoke the next middleware.
     * @return \Psr\Http\Message\ResponseInterface A response
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getParam('plugin') === 'DebugKit') {
            return $handler->handle($request);
        }

        $this->_loop = (int)$request->getSession()->read('I18nUrl.loop');

        $lang = $request->getParam('lang');

        if (empty($lang)) {
            $lang = Configure::read('I18n.default');
        }

        $accepted = $this->isAcceptedLanguage($lang);

        if ($accepted) {
            return $this->_setLocale($lang, $request, $handler);
        } else {
            $lang = $this->getFirstAcceptedLanguage($request);
        }

        if ($this->_loop > 1) {
            return $this->_setLocale($lang, $next, $request, $response);
        }

        try {
            $ary = array_merge(['controller' => $request->getParam('controller'), 'action' => $request->getParam('action'), 'lang' => $lang], $request->getParam('pass'));
            $newLocation = Router::url($ary, true);

            // Antiloop system
            $this->_loop++;
            $request->getSession()->write('I18nUrl.loop', $this->_loop);

            $r = $response->withLocation($newLocation);
        } catch (MissingRouteException $e) {
            debug($e);
            exit;
        }

        // return new response object with location
        $response = $handler->handle($request);
    }

    /**
     * set application Locale
     * @param string                                    $lang       new lang locale
     * @param callable                                  $next       Callback to invoke the next middleware.
     * @param \Psr\Http\Message\ServerRequestInterface  $request    The request.
     * @param \Psr\Http\Message\ResponseInterface       $response   The response.
     * @return \Psr\Http\Message\ResponseInterface      A response
     */
    protected function _setLocale($lang, ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Set locale
        $locale = $this->_locales[$lang];
        I18n::setLocale($locale);

        // date format
        $dateFormat = self::$_formats[$locale]['date'];
        $timeFormat = self::$_formats[$locale]['time'];
        $dateTimeFormat = $dateFormat . ' ' . $timeFormat;

        CakeDate::setToStringFormat($dateFormat);
        FrozenDate::setToStringFormat($dateFormat);
        Type::build('date')->useLocaleParser()->setLocaleFormat($dateFormat);

        Time::setToStringFormat($dateTimeFormat);
        FrozenTime::setToStringFormat($dateTimeFormat);
        Type::build('datetime')->useLocaleParser()->setLocaleFormat($dateTimeFormat);

        $request->getSession()->delete('I18nUrl');

        return $handler->handle($request);
    }

    /**
     * get current locale. And clean version
     * @param  bool $clean clean 2 letters version
     * @return string         fr | fr_FR locale code
     */
    public static function getLocale($clean = false)
    {
        if (!$clean) {
            return I18n::getLocale();
        }

        return strtolower(substr(I18n::getLocale(), 0, 2));
    }

    /**
     * alternate locale code
     * @param  bool $clean clean 2 letters version
     * @return string
     */
    public static function getAlternateLocale($clean = false)
    {
        if (I18n::getLocale() === 'fr_FR') {
            return $clean ? 'en' : 'en_US';
        }

        return $clean ? 'fr' : 'fr_FR';
    }

    public function isAcceptedLanguage($lang)
    {
        return $lang === false ? false : array_key_exists($lang, $this->_locales);
    }

    public function getFirstAcceptedLanguage($request)
    {
        $browserLanguages = explode(',', $request->getHeaderLine('Accept-Language'));

        $byQuality = [];
        foreach ($browserLanguages as $value) {
            if (preg_match('/q=/i', $value)) {
                list($lang, $q) = explode(';', $value);
                list($t, $quality) = explode('=', $q);
            } else {
                $lang = $value;
                $quality = 1;
            }

            $lang = trim($lang);
            $quality = trim($quality);

            if (!isset($byQuality[$quality])) {
                $byQuality[$quality] = [];
            }

            array_push($byQuality[$quality], $lang);
        }

        krsort($byQuality);

        foreach ($byQuality as $langs) {
            $accepted = array_filter($langs, 'self::isAcceptedLanguage');
            if (!empty($accepted)) {
                return $accepted[array_rand($accepted)];
            }
        }

        // return first _locales
        return current(array_keys($this->_locales));
    }
}
