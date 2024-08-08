import {__} from '@wordpress/i18n';
import {registerPlugin} from '@wordpress/plugins';
import {PluginDocumentSettingPanel} from '@wordpress/edit-post';
import {store as blockEditorStore} from '@wordpress/block-editor';
import {useSelect, useDispatch} from '@wordpress/data';
import {useEntityProp} from '@wordpress/core-data';
import {Button, ButtonGroup} from '@wordpress/components';
import {store as editorStore} from '@wordpress/editor';
import {store as noticesStore} from '@wordpress/notices';

const PaywallSettings = ({postType}) => {
  const [meta, setMeta] = useEntityProp('postType', postType, 'meta');
  const paywall = meta ? meta['paywall'] : null;

  const hasPaywallBlocks = useSelect((select) => {
    const {getBlocksByName} = select(blockEditorStore);
    return getBlocksByName('wp-paywall/paywall').length > 0;
  }, []);

  const hasPaywallBlocksOptOut = hasPaywallBlocks && paywall === 'optout';
  const hasPaywallBlocksNotApplied =
    hasPaywallBlocks && !paywall && postType === 'page';

  const noticeId = 'paywall-block-warning';
  const {createWarningNotice, removeNotice} = useDispatch(noticesStore);

  if (hasPaywallBlocksOptOut) {
    removeNotice(noticeId);
    createWarningNotice(
      __(
        'Warning! This post uses "Paywall" blocks but is set to opt-out of paywall so the blocks will be shown to users.',
        'wp-paywall',
      ),
      {
        id: noticeId,
      },
    );
  }
  if (hasPaywallBlocksNotApplied) {
    removeNotice(noticeId);
    createWarningNotice(
      __(
        'Warning! This post uses "Paywall" blocks but by default pages do not have paywall applied, you need to force it in the settings bar to take effect.',
        'wp-paywall',
      ),
      {
        id: noticeId,
      },
    );
  }

  const onChange = (value) => {
    if (value !== 'optout') {
      removeNotice(noticeId);
    }
    setMeta({
      ...meta,
      paywall: value,
    });
  };

  return (
    <>
      <ButtonGroup>
        <Button isPressed={!paywall} onClick={() => onChange(null)}>
          {__('Default', 'wp-paywall')}
        </Button>
        <Button
          isPressed={paywall === 'optout'}
          isDestructive={hasPaywallBlocksOptOut}
          onClick={() => onChange('optout')}
        >
          {__('Opt-out', 'wp-paywall')}
        </Button>
        <Button
          isPressed={paywall === 'optin'}
          onClick={() => onChange('optin')}
        >
          {__('Force Paywall', 'wp-paywall')}
        </Button>
      </ButtonGroup>
    </>
  );
};

registerPlugin('paywall', {
  open: true,
  render: () => {
    const postType = useSelect((select) =>
      select(editorStore).getCurrentPostType(),
    );

    if (!['post', 'page'].includes(postType)) {
      return <></>;
    }

    return (
      <PluginDocumentSettingPanel
        name="panel"
        title={__('Paywall', 'wp-paywall')}
        description={__('Paywall restrictions for the content', 'wp-paywall')}
      >
        <PaywallSettings postType={postType} />
      </PluginDocumentSettingPanel>
    );
  },
});
