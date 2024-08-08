/**
 * WordPress dependencies
 */
import {createBlock} from '@wordpress/blocks';

const transforms = {
  from: [
    {
      type: 'block',
      isMultiBlock: true,
      blocks: ['*'],
      __experimentalConvert(blocks) {
        const alignments = ['wide', 'full'];

        // Determine the widest setting of all the blocks to be grouped
        const widestAlignment = blocks.reduce((accumulator, block) => {
          const {align} = block.attributes;
          return alignments.indexOf(align) > alignments.indexOf(accumulator) ?
              align
            : accumulator;
        }, undefined);

        // Clone the Blocks to be Grouped
        // Failing to create new block references causes the original blocks
        // to be replaced in the switchToBlockType call thereby meaning they
        // are removed both from their original location and within the
        // new group block.
        const innerBlocks = blocks.map((block) => {
          return createBlock(block.name, block.attributes, block.innerBlocks);
        });

        return createBlock(
          'wp-paywall/paywall',
          {align: widestAlignment},
          innerBlocks,
        );
      },
    },
  ],
};

export default transforms;
