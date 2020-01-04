<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Category Entity
 *
 * @property string $id
 * @property string $category_id
 * @property string $foreign_key
 * @property string $model
 * @property int $record_count
 * @property string $user_id
 * @property int $lft
 * @property int $rght
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\Category[] $categories
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Categorized[] $categorized
 * @property \App\Model\Entity\Package[] $packages
 */
class Category extends Entity
{
    protected static $_categoryColors = [];

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        '*' => true,
        'id' => false,
    ];

    public function getColor()
    {
        $slug = $this->slug;
        if (empty(static::$_categoryColors[$slug])) {
            static::$_categoryColors[$slug] = $this->stringToColor($slug);
        }

        return static::$_categoryColors[$slug];
    }

    public function stringToColor($text, $minBrightness = 50, $spec = 9)
    {
        // Check inputs
        if (!is_int($minBrightness)) {
            throw new Exception("$minBrightness is not an integer");
        }
        if (!is_int($spec)) {
            throw new Exception("$spec is not an integer");
        }
        if ($spec < 2 || $spec > 10) {
            throw new Exception("$spec is out of range");
        }
        if ($minBrightness < 0 || $minBrightness > 255) {
            throw new Exception("$minBrightness is out of range");
        }

        $hash = md5($text); // Gen hash of text
        $colors = [];
        for ($i = 0; $i < 3; $i++) {
            // convert hash into 3 decimal values between 0 and 255
            $colors[$i] = max([round(((hexdec(substr($hash, $spec * $i, $spec))) / hexdec(str_pad('', $spec, 'F'))) * 255), $minBrightness]);
        }

        // only check brightness requirements if minBrightness is about 100
        if ($minBrightness > 0) {
            // loop until brightness is above or equal to minBrightness
            while (array_sum($colors) / 3 < $minBrightness) {
                for ($i = 0; $i < 3; $i++) {
                    $colors[$i] += 10; // increase each color by 10
                }
            }
        }

        $output = '';

        for ($i = 0; $i < 3; $i++) {
            // convert each color to hex and append to output
            $output .= str_pad(dechex($colors[$i]), 2, 0, STR_PAD_LEFT);
        }

        return '#' . $output;
    }
}
