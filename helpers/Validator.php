cat > helpers/Validator.php << 'EOF'
<?php
declare(strict_types=1);

class Validator
{
    private array $errors = [];

    public function __construct(private array $input) {}

    public function required(string $field, string $label = ''): static
    {
        $label = $label ?: $field;
        if (!isset($this->input[$field]) || trim((string)$this->input[$field]) === '') {
            $this->errors[$field][] = "El campo '{$label}' es obligatorio.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max): static
    {
        if (isset($this->input[$field]) && mb_strlen((string)$this->input[$field]) > $max) {
            $this->errors[$field][] = "El campo '{$field}' no puede superar {$max} caracteres.";
        }
        return $this;
    }

    public function numeric(string $field, float $min = 0): static
    {
        if (isset($this->input[$field]) && $this->input[$field] !== '') {
            if (!is_numeric($this->input[$field])) {
                $this->errors[$field][] = "El campo '{$field}' debe ser numérico.";
            } elseif ((float)$this->input[$field] < $min) {
                $this->errors[$field][] = "El campo '{$field}' debe ser >= {$min}.";
            }
        }
        return $this;
    }

    public function inList(string $field, array $allowed): static
    {
        if (isset($this->input[$field]) && $this->input[$field] !== '') {
            if (!in_array($this->input[$field], $allowed, true)) {
                $this->errors[$field][] = "El campo '{$field}' debe ser uno de: " . implode(', ', $allowed) . ".";
            }
        }
        return $this;
    }

    public function email(string $field): static
    {
        if (isset($this->input[$field]) && $this->input[$field] !== '') {
            if (!filter_var($this->input[$field], FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field][] = "El campo '{$field}' debe ser un email válido.";
            }
        }
        return $this;
    }

    public function fails(): bool { return !empty($this->errors); }
    public function errors(): array { return $this->errors; }
    public function validated(): array { return $this->input; }
}
EOF
