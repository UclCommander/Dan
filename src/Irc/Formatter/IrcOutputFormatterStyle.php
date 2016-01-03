<?php

namespace Dan\Irc\Formatter;

use Symfony\Component\Console\Formatter\OutputFormatterStyleInterface;

class IrcOutputFormatterStyle implements OutputFormatterStyleInterface
{
    protected $char = "\x03";

    private static $availableForegroundColors = [
        'error'         => ['set' => '04', 'unset' => ''],
        'white'         => ['set' => '00', 'unset' => ''],
        'black'         => ['set' => '01', 'unset' => ''],
        'blue'          => ['set' => '02', 'unset' => ''],
        'green'         => ['set' => '03', 'unset' => ''],
        'red'           => ['set' => '04', 'unset' => ''],
        'maroon'        => ['set' => '05', 'unset' => ''],
        'purple'        => ['set' => '06', 'unset' => ''],
        'orange'        => ['set' => '07', 'unset' => ''],
        'yellow'        => ['set' => '08', 'unset' => ''],
        'light_green'   => ['set' => '09', 'unset' => ''],
        'cyan'          => ['set' => '10', 'unset' => ''],
        'light_cyan'    => ['set' => '11', 'unset' => ''],
        'light_blue'    => ['set' => '12', 'unset' => ''],
        'pink'          => ['set' => '13', 'unset' => ''],
        'gray'          => ['set' => '14', 'unset' => ''],
        'light_gray'    => ['set' => '15', 'unset' => ''],
        'default'       => ['set' => '',   'unset' => ''],
    ];

    private static $availableBackgroundColors = [
        'black'     => ['set' => 40, 'unset' => ''],
        'red'       => ['set' => 41, 'unset' => ''],
        'green'     => ['set' => 42, 'unset' => ''],
        'yellow'    => ['set' => 43, 'unset' => ''],
        'blue'      => ['set' => 44, 'unset' => ''],
        'magenta'   => ['set' => 45, 'unset' => ''],
        'cyan'      => ['set' => 46, 'unset' => ''],
        'white'     => ['set' => 47, 'unset' => ''],
        'default'   => ['set' => 49, 'unset' => ''],
    ];
    private static $availableOptions = [
        'bold'      => ['set' => "\x02", 'unset' => ''],
        'b'         => ['set' => "\x02", 'unset' => ''],
        'underline' => ['set' => "\x1F", 'unset' => ''],
        'u'         => ['set' => "\x1F", 'unset' => ''],
        'italic'    => ['set' => "\x16", 'unset' => ''],
        'i'         => ['set' => "\x16", 'unset' => ''],
        'normal'    => ['set' => "\x0F", 'unset' => ''],
        'n'         => ['set' => "\x0F", 'unset' => ''],
    ];

    private $foreground;
    private $background;
    private $options = [];

    /**
     * Initializes output formatter style.
     *
     * @param string|null $foreground The style foreground color name
     * @param string|null $background The style background color name
     * @param array       $options    The style options
     *
     * @api
     */
    public function __construct($foreground = null, $background = null, array $options = [])
    {
        if (null !== $foreground) {
            $this->setForeground($foreground);
        }
        if (null !== $background) {
            $this->setBackground($background);
        }
        if (count($options)) {
            $this->setOptions($options);
        }
    }

    /**
     * Sets style foreground color.
     *
     * @param string|null $color The color name
     *
     * @throws \InvalidArgumentException When the color name isn't defined
     *
     * @api
     */
    public function setForeground($color = null)
    {
        if (null === $color) {
            $this->foreground = null;

            return;
        }

        if (!isset(static::$availableForegroundColors[$color])) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid foreground color specified: "%s". Expected one of (%s)',
                $color,
                implode(', ', array_keys(static::$availableForegroundColors))
            ));
        }

        $this->foreground = static::$availableForegroundColors[$color];
    }

    /**
     * Sets style background color.
     *
     * @param string|null $color The color name
     *
     * @throws \InvalidArgumentException When the color name isn't defined
     *
     * @api
     */
    public function setBackground($color = null)
    {
        if (null === $color) {
            $this->background = null;

            return;
        }

        if (!isset(static::$availableBackgroundColors[$color])) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid background color specified: "%s". Expected one of (%s)',
                $color,
                implode(', ', array_keys(static::$availableBackgroundColors))
            ));
        }

        $this->background = static::$availableBackgroundColors[$color];
    }

    /**
     * Sets some specific style option.
     *
     * @param string $option The option name
     *
     * @throws \InvalidArgumentException When the option name isn't defined
     *
     * @api
     */
    public function setOption($option)
    {
        if (!isset(static::$availableOptions[$option])) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid option specified: "%s". Expected one of (%s)',
                $option,
                implode(', ', array_keys(static::$availableOptions))
            ));
        }

        if (!in_array(static::$availableOptions[$option], $this->options)) {
            $this->options[] = static::$availableOptions[$option];
        }
    }

    /**
     * Unsets some specific style option.
     *
     * @param string $option The option name
     *
     * @throws \InvalidArgumentException When the option name isn't defined
     */
    public function unsetOption($option)
    {
        if (!isset(static::$availableOptions[$option])) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid option specified: "%s". Expected one of (%s)',
                $option,
                implode(', ', array_keys(static::$availableOptions))
            ));
        }

        $pos = array_search(static::$availableOptions[$option], $this->options);
        if (false !== $pos) {
            unset($this->options[$pos]);
        }
    }

    /**
     * Sets multiple style options at once.
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = [];

        foreach ($options as $option) {
            $this->setOption($option);
        }
    }

    /**
     * Applies the style to a given text.
     *
     * @param string $text The text to style
     *
     * @return string
     */
    public function apply($text)
    {
        $setCodes = [];
        $unsetCodes = [];

        if ($this->foreground !== null) {
            $setCodes[] = $this->char.$this->foreground['set'];
            $unsetCodes[] = $this->foreground['unset'];
        }

        if ($this->background !== null) {
            $setCodes[] = $this->char.$this->background['set'];
            $unsetCodes[] = $this->background['unset'];
        }

        if (count($this->options)) {
            foreach ($this->options as $option) {
                $setCodes[] = $option['set'];
                $unsetCodes[] = $option['unset'];
            }

            return sprintf("%s%s\x0F", implode('', $setCodes), $text);
        }

        if (count($setCodes) === 0) {
            return $text;
        }

        return sprintf("%s%s\x03", implode(',', $setCodes), $text);
    }
}
