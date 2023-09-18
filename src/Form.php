<?php
namespace Cvy\WP\Formidable;

abstract class Form
{
  use \Cvy\DesignPatterns\tSingleton;

  protected function __construct()
  {
    throw new \Exception( 'This method is abstract and must be overriden!' );
  }

  abstract static public function get_id() : int;

  abstract static protected function get_field_keys_prefix() : string;

  static public function get_entries( array $query_args = [] ) : array
  {
    $query_args = array_merge([
      'form_id' => static::get_id(),
    ], $query_args );

    $wrapped_entries = [];

    foreach ( \FrmEntry::getAll( $query_args ) as $original_entry )
    {
      $wrapped_entries[] = static::get_entry( $original_entry->id );
    }

    return $wrapped_entries;
  }

  static public function unprefix_field_key( string $prefixed_key ) : string
  {
    return str_replace( static::get_field_keys_prefix(), '', $prefixed_key );
  }

  static public function get_field_id( string $unprefixed_field_key ) : int
  {
    $field_key = static::get_field_keys_prefix() . $unprefixed_field_key;

    return (int) \FrmField::getOne( $field_key )->id;
  }

  static public function get_entry( string $selector ) : Entry
  {
    return new Entry( $selector, static::get_field_keys_prefix() );
  }
}
