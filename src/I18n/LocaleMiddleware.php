<?php
namespace I18nUrl\I18n;

use Cake\I18n\I18n;
use Cake\Http\Exception\NotFoundException;
use Cake\Routing\Exception\MissingRouteException;
use I18nUrl\Routing\Router;
use Ecl\I18n\DateTimeFormat;
use Locale;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LocaleMiddleware
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
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
    {
        if ($request->getParam('plugin') === 'DebugKit') {
            return $next($request, $response);
        }

        $this->_loop = (int)$request->getSession()->read('I18nUrl.loop');

        $lang = $request->getParam('lang');
        $accepted = $this->isAcceptedLanguage($lang);

        if ($lang !== false && $accepted) {
            return $this->_setLocale($lang, $next, $request, $response);
        }

        if ($lang === false || !$accepted) {
            $lang = $this->getFirstAcceptedLanguage($request);
        }

        if ($this->_loop > 1) {
            return $this->_setLocale($lang, $next, $request, $response);
        }

        try {
            $ary = array_merge(['controller' => $request->getParam('controller'), 'action' => $request->getParam('action'), 'lang' => $lang], $request->pass);
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
        return $response = $r;
    }

    /**
     * set application Locale
     * @param string                                    $lang       new lang locale
     * @param callable                                  $next       Callback to invoke the next middleware.
     * @param \Psr\Http\Message\ServerRequestInterface  $request    The request.
     * @param \Psr\Http\Message\ResponseInterface       $response   The response.
     * @return \Psr\Http\Message\ResponseInterface      A response
     */
    protected function _setLocale($lang, $next, $request, $response)
    {
        // Set locale
        $locale = $this->_locales[$lang];
        I18n::setLocale($locale);

        // date format
        DateTimeFormat::setDateTimeFormat(self::$_formats[$locale]['date'], self::$_formats[$locale]['time']);
        DateTimeFormat::setTimezone(self::$_formats[$locale]['timezone']);

        $request->getSession()->delete('I18nUrl');

        return $next($request, $response);
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
