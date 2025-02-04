/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import {
	Button,
	ExternalLink,
	__unstableCompositeItem as CompositeItem, // eslint-disable-line
	VisuallyHidden,
} from '@wordpress/components';

import { BlockPreview } from '@wordpress/block-editor';
import { cloneBlock } from '@wordpress/blocks';
import { useInstanceId } from '@wordpress/compose';
import { useDispatch, dispatch } from '@wordpress/data';

/**
 * Renders the block pattern 'card'.
 *
 * @since 0.1.0
 * @param {Object} props All the props passed to this function
 * @return {string}      Return the rendered JSX
 */
export default function Pattern( props ) {
	const {
		pattern,
		onInsertPattern,
		viewportWidth,
		composite,
		isBlock,
		clientId,
	} = props;

	const { title, categories = [], blocks } = pattern; // eslint-disable-line
	const { createSuccessNotice } = useDispatch( 'core/notices' );
	const instanceId = useInstanceId( Pattern );
	const descriptionId = `preview-pattern-card__info-description-${ instanceId }`;

	function insertPattern() {
		onInsertPattern( blocks.map( ( block ) => cloneBlock( block ) ) );

		// If the inserter was rendered from a block, we need to remove that
		// original block.
		if ( isBlock ) {
			dispatch( 'core/block-editor' ).removeBlock( clientId );
		}

		createSuccessNotice(
			sprintf(
				// translators: placeholder: block pattern title.
				__( 'Block pattern "%s" inserted.', 'ea-styles-library' ),
				pattern.title
			),
			{ type: 'snackbar' }
		);
	}

	const baseClassName = 'block-pattern-explorer__preview-pattern-list__item';

	return (
		<div
			className={ baseClassName }
			aria-label={ pattern.title }
			aria-describedby={
				pattern?.description ? descriptionId : undefined
			}
			>
			<CompositeItem
				role="option"
				as="div"
				{ ...composite }
				className={ `${ baseClassName }-preview` }
				// onClick={ insertPattern }
			>
				{ pattern?.content && (
					<BlockPreview
						blocks={ blocks }
						viewportWidth={ viewportWidth }
					/>
				) }

				{ pattern?.previewURL && pattern?.content === '' && (
					<img src={ pattern?.previewURL } />
				)}
			</CompositeItem>

			<div className={ `${ baseClassName }-actions` }>
				<div className={ `${ baseClassName }-title` }>{ title }</div>

				{ pattern?.isPaid &&(
					<p className={ `${ baseClassName }-cost premium` } > { __( 'Premium', 'ea-styles-library' ) } </p>
				)}

				{ !! pattern.description && (
					<VisuallyHidden id={ descriptionId }>
						{ pattern.description }
					</VisuallyHidden>
				) }

				{ pattern?.content && (
					<Button isSecondary onClick={ insertPattern }>
						{ __( 'Add Pattern', 'ea-styles-library' ) }
					</Button>
				)}

				{ pattern?.content == '' && pattern?.demoURL && (
					<ExternalLink className={ `${ baseClassName }-demo-link` } href={ pattern?.demoURL }>
						{ __( 'See Demo', 'ea-styles-library' ) }
					</ExternalLink>
				)}

			</div>

			{ pattern?.content == '' &&(
				<div className={ `${ baseClassName }-overlay` } >
					<span className="dashicon dashicons dashicons-lock"> </span>
				</div>
			)}
		</div>
	);
}
