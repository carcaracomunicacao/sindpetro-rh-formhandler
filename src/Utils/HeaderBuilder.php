<?php

namespace App\Utils;

class HeaderBuilder
{
    private string $lang = "pt-br";
    private string $title = "Gerenciador de Formulários";
    private string $description = "Sistema de gestão de formulários online";
    private string $ogImage = "/assets/img/default-share.jpg"; // Caminho padrão
    private array $keywords = ["php", "bootstrap", "formulários"];

    public function setLang(string $lang): self
    {
        $this->lang = $lang;
        return $this;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setOgImage(string $url): self
    {
        $this->ogImage = $url;
        return $this;
    }

    public function render(): void
    {
?>

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">

            <title><?= htmlspecialchars($this->title) ?></title>
            <meta name="description" content="<?= htmlspecialchars($this->description) ?>">
            <meta name="keywords" content="<?= implode(', ', $this->keywords) ?>">

            <meta property="og:type" content="website">
            <meta property="og:title" content="<?= htmlspecialchars($this->title) ?>">
            <meta property="og:description" content="<?= htmlspecialchars($this->description) ?>">
            <meta property="og:image" content="<?= $this->ogImage ?>">
            <meta property="og:locale" content="pt_BR">

            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

            <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
        </head>
<?php
    }

    public function getLang(): string
    {
        return $this->lang;
    }
}
