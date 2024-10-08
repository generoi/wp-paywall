/** @wordpress */
import {registerBlockType} from '@wordpress/blocks';
import {InnerBlocks} from '@wordpress/block-editor';

import edit from './edit';
import meta from './block.json';
import transforms from './transforms';

registerBlockType(meta.name, {
  ...meta,
  edit,
  save() {
    return <InnerBlocks.Content />;
  },
  transforms,
});
