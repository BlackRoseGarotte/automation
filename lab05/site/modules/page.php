<?php

declare(strict_types=1);

class Page
{
    private string $template;

    public function __construct(string $template)
    {
        if (!is_file($template) || !is_readable($template)) {
            throw new InvalidArgumentException('Template does not exist or is unreadable');
        }
        $this->template = $template;
    }

    public function Render(array $data): string
    {
        $content = file_get_contents($this->template);
        if ($content === false) {
            throw new RuntimeException('Cannot read template');
        }
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}',
                htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), $content);
        }
        return $content;
    }
}
