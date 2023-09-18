<?php
namespace Cvy\WP\Formidable;

class Entry
{
  protected $selector;

  protected $field_keys_prefix;

  public function __construct( string $selector, string $field_keys_prefix )
  {
    $this->selector = $selector;
    $this->field_keys_prefix = $field_keys_prefix;
  }

  public function get_selector() : string
  {
    return $this->selector;
  }

  public function get_values() : array
  {
    $values = [];

    $entry_meta = \FrmEntryMeta::get_entry_meta_info( $this->get_selector() );

    foreach ( $entry_meta as $meta_item )
    {
      $field = \FrmField::getOne( $meta_item->field_id );

      // Formidable stores not only fields in metadata but some other data as well
      if ( ! $field )
      {
        continue;
      }

      $key = str_replace( $this->field_keys_prefix, '', $field->field_key );

      $values[ $key ] = $this->normalize_field_value( $meta_item->meta_value, $field );
    }

    return $values;
  }

  protected function normalize_field_value( $value, \stdClass $field )
  {
    if ( $field->type === 'divider' )
    {
      $value = $this->normalize_repeater_field_value( $value, $field );
    }
    else if ( $field->type === 'number' || is_numeric( $value ) )
    {
      $value =
        (float) $value == (int) $value ?
        (int) $value :
        (float) $value;
    }

    return $value;
  }

  protected function normalize_repeater_field_value( string $value, \stdClass $field ) : array
  {
    $sub_entries_ids = unserialize( $value );

    $value = [];

    foreach ( $sub_entries_ids as $sub_entry_id )
    {
      $sub_entry = new Entry( $sub_entry_id, $this->field_keys_prefix );

      $value[ $sub_entry_id ] = $sub_entry->get_values();
    }

    return $value;
  }
}
