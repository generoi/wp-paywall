import {useBlockProps, useInnerBlocksProps} from '@wordpress/block-editor';

import './editor.css';

function Edit() {
  const blockProps = useBlockProps({
    className: 'is-paywall wp-block-group',
  });
  const innerBlockProps = useInnerBlocksProps(blockProps, {});

  return <div {...innerBlockProps} />;
}

export default Edit;
