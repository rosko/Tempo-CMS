{if $unit.title}
    <h2>{$unit.title}</h2>
{/if}

{$content.text}

{if $content.author}
    <p>{t text='Author' cat='UnitText.unit'}: {$content.author}</p>
{/if}

