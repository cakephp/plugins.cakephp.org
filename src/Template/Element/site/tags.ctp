<?php
use Cake\Collection\Collection;

if (empty($package->tags)) {
    return;
}

$tags = (array)(new Collection(explode(',', $package->tags)))
        ->map(function($tag) {
            list($key, $value) = explode(':', $tag, 2);
            if (in_array($key, ['has', 'keyword'])) {
                $key = sprintf('%s[]', $key);
            }
            return [
                'key' => $key,
                'name' => $tag,
                'value' => $value,
            ];
            return strpos($tag, ':') === false ? sprintf('has:%s', $tag) : $tag;
        })
        ->toArray();
?>

<?php foreach ($tags as $tag) : ?>
<a href="/packages?<?php echo $tag['key']; ?>=<?php echo $tag['value']; ?>" class="label category-label" style="background-color:black;color:white">
    <?php echo $tag['name']; ?>
</a>&nbsp;
<?php endforeach; ?>
