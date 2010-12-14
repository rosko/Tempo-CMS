{if $unit.title}
    <h2>{$unit.title}</h2>
{/if}

{$content.text}

{if $content.author}
    <p>{t text='Author'}: {$content.author}</p>
{/if}

