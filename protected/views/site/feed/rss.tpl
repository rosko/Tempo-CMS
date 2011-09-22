<?xml version="1.0"?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
   <channel>
      <title>{$channel.title}</title>
      <link>{$channel.link|escape}</link>
      <description>{if $channel.description}{$channel.description}{else}{$settings.global.sitename}{/if}</description>
      <language>{$channel.language}</language>
      <pubDate>{date("r", strtotime($channel.updated))}</pubDate>
      <lastBuildDate>{date("r", strtotime($channel.updated))}</lastBuildDate>
      <atom:link href="{$rssLink|escape}" rel="self" type="application/rss+xml" />
{foreach $items as $item}
      <item>
          <title>{$item.title}</title>
          <link>{$item.link}</link>
          <description><![CDATA[{strip}
{if $item.image}<p><img src="{$item.image}" width="200" /></p>{else}{$item.description}{/if}
{/strip}]]></description>
          <pubDate>{date("r", strtotime($item.updated))}</pubDate>
          <guid isPermaLink="true">{$item.link}</guid>
      </item>
{/foreach}
   </channel>
</rss>