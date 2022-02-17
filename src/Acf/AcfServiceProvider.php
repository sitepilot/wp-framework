<?php

namespace Sitepilot\WpFramework\Acf;

use Sitepilot\WpFramework\Support\ServiceProvider;

class AcfServiceProvider extends ServiceProvider
{
    /**
     * Get option value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed|null
     */
    public function option(string $key, $default = null)
    {
        if (function_exists('get_field')) {
            return get_field($key, 'option') ?: $default;
        }

        return null;
    }

    /**
     * Get field value.
     *
     * @param string $key
     * @param mixed $default
     * @param integer $post_id
     * @return mixed|null
     */
    public function field(string $key, $default = null, $post_id = false)
    {
        if (function_exists('get_field')) {
            return get_field($key, $post_id) ?: $default;
        }

        return null;
    }

    /**
     * Get mapped field value.
     *
     * @param string $key
     * @param array $map
     * @param string|null $default
     * @return void
     */
    function map_field(string $key, array $map, string $default = null): string
    {
        return $map[$this->field($key, $default)] ?? '';
    }

    /**
     * Add option page.
     * 
     * @see https://www.advancedcustomfields.com/resources/options-page/
     *
     * @param array $config
     * @return void 
     */
    public function add_option_page(array $config): void
    {
        add_action('acf/init', function () use ($config) {
            acf_add_options_page($config);
        });
    }

    /**
     * Add sub option page.
     * 
     * @see https://www.advancedcustomfields.com/resources/options-page/
     *
     * @param array $config
     * @return void 
     */
    public function add_sub_option_page(array $config): void
    {
        add_action('acf/init', function () use ($config) {
            acf_add_options_sub_page($config);
        });
    }

    /**
     * Add option page fields.
     *
     * @param string $slug
     * @param string $title
     * @param array $fields
     * @return void
     */
    public function add_option_page_fields(string $slug, string $title, array $fields = [])
    {
        foreach ($fields as &$field) {
            $field['key'] = $this->format_key($slug . '_' . $field['name'] ?? '');
        }

        $group = [
            'key' => $this->format_key('group_' . $slug . '_' . uniqid()),
            'title' => $title,
            'fields' => $fields,
            'location' => array(
                array(
                    array(
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => $slug,
                    ),
                ),
            )
        ];

        add_action('acf/init', function () use ($group) {
            acf_add_local_field_group($group);
        });
    }

    /**
     * Add theme block.
     * 
     * @see https://www.advancedcustomfields.com/resources/acf_register_block_type/
     *
     * @param string $name
     * @param array $block
     * @param array $fields
     * @return void
     */
    public function add_block(array $block): void
    {
        if (empty($block['title'])) {
            $block['title'] = ucfirst($block['name']);
        }

        if (empty($block['render_template'])) {
            $block['render_template'] = $this->app->get_path('blocks/' . str_replace('sp-', '', $block['name']) . '.php');
        }

        if (!empty($block['fields']) && is_array($block['fields'])) {
            $this->add_block_fields($block['name'], $block['fields']);
            unset($block['fields']);
        }

        add_action('acf/init', function () use ($block) {
            acf_register_block_type($block);
        });
    }

    /**
     * Add block fields.
     *
     * @see https://www.advancedcustomfields.com/resources/register-fields-via-php/
     * 
     * @param string $name
     * @param array $fields
     * @return void
     */
    public function add_block_fields(string $name, array $fields): void
    {
        foreach ($fields as &$field) {
            if (empty($field['key'])) {
                $field['key'] = $this->format_key($name . '_' . $field['name'] ?? '');
            }
        }

        $group = [
            'key' => $this->format_key('group_' . $name . '_' . uniqid()),
            'fields' => $fields,
            'location' => array(
                array(
                    array(
                        'param' => 'block',
                        'operator' => '==',
                        'value' => "acf/$name",
                    ),
                ),
            )
        ];

        add_action('acf/init', function () use ($group) {
            acf_add_local_field_group($group);
        });
    }

    /**
     * Get block attributes.
     *
     * @param array $block
     * @param array $classes
     * @return string
     */
    public function get_block_attributes(array $block, array $classes = array()): string
    {
        $name = 'sp-block-' . str_replace('acf/sp-', '', $block['name']);

        $id = $block['id'];
        if (!empty($block['anchor'])) {
            $id = $block['anchor'];
        }

        array_unshift($classes, $name);

        if (!empty($block['className'])) {
            array_push($classes, $block['className']);
        }

        if (!empty($block['textColor'])) {
            array_push($classes, 'has-' . $block['textColor'] . '-color');
        }

        if (!empty($block['backgroundColor'])) {
            array_push($classes, 'has-' . $block['backgroundColor'] . '-background-color');
        }

        if (!empty($block['gradient'])) {
            array_push($classes, 'has-' . $block['gradient'] . '-gradient-background');
        }

        if (!empty($block['align'])) {
            array_push($classes, 'align' . $block['align']);
        }

        if (!empty($block['fontSize'])) {
            array_push($classes, 'has-' . $block['fontSize'] . '-font-size');
        }

        return "class=\"" . implode(' ', $classes) . "\" id=\"{$id}\"";
    }

    /**
     * Get block style.
     *
     * @param array $block
     * @return string
     */
    public function get_block_style(array $block): string
    {
        $match = array();

        if (preg_match('/is-style-[a-zA-Z0-9_-]*/', $block['className'] ?? '', $match)) {
            return str_replace(['is-style-sp-', 'is-style-'], '', reset($match));
        }

        return '';
    }

    /**
     * Get block classes.
     *
     * @param array $block
     * @param array $classes
     * @return array
     */
    public function get_block_classes(array $block, array $classes): array
    {
        $keys = array();

        foreach ($classes as $item) {
            foreach (array_keys($item) as $key) {
                if (!in_array($key, $keys)) $keys[] = $key;
            }
        }

        foreach ($keys as $key) {
            $return[$key] =  implode(" ", $classes[$this->get_block_style($block)][$key] ?? $classes['default'][$key] ?? []);
        }

        return $return;
    }

    /**
     * Get ACF inner blocks HTML.
     *
     * @param array $allowed_blocks An array of block names that restricted the types of content that can be inserted.
     * @param array $template A structured array of block content as documented in the CPT block template guide.
     * @param string $lock Locks the template content, vailable settings are "all" or "insert".
     * @return string
     */
    public function get_inner_blocks_html(array $allowed_blocks = [], array $template = [], string $lock = ''): string
    {
        $attributes = [];

        if ($allowed_blocks) {
            $attributes[] = 'allowedBlocks="' . esc_attr(json_encode($allowed_blocks)) . '"';
        }

        if ($template) {
            $attributes[] = 'template="' . esc_attr(json_encode([$template])) . '"';
        }

        if ($lock) {
            $attributes[] = 'templateLock="' . $lock . '"';
        }

        return '<InnerBlocks ' . implode(' ', $attributes) . '/>';
    }

    /**
     * Format key.
     *
     * @param string $key
     * @return string
     */
    private function format_key(string $key): string
    {
        return str_replace('-', '_', $key);
    }
}
