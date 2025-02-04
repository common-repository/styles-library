/**
 * External dependencies
 */
import { map } from 'lodash';

/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';
import { cloneBlock } from '@wordpress/blocks';
import { useDispatch, useSelect } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';
import { store as noticesStore } from '@wordpress/notices';
import { store as blockEditorStore } from '@wordpress/block-editor';

/**
 * Retrieves the block patterns inserter state.
 *
 * @param {Function} onInsert     function called when inserter a list of blocks.
 * @param {string=}  rootClientId Insertion's root client ID.
 *
 * @return {Array} Returns the patterns state. (patterns, categories, onSelect handler)
 */
const usePatternsState = ( onInsert, rootClientId ) => {
	const { patterns, patternCategories, patternTypesAndPatterns } = useSelect(
		( select ) => {
			const { __experimentalGetAllowedPatterns, getSettings } = select(
				blockEditorStore
			);

			// Fetch any register pattern category types with the custom REST
			// API endpoint. Eventually replace with core functionality.
			const { getEntityRecord } = select( 'core' );
			const categoryTypes = getEntityRecord(
				'easl/v1',
				'patternTypesAndPatterns'
			);

			return {
				patterns: __experimentalGetAllowedPatterns( rootClientId ),
				patternCategories: getSettings()
					.__experimentalBlockPatternCategories,
				patternTypesAndPatterns:
					categoryTypes?.patternTypesAndPatterns?.data ?? false,
				// This is new functionality and will need to ultimately be added to the
				// Gutenberg Patterns API. Category Types allow theme/plugin developers to
				// group pattern categories together in the new Pattern Explorer.
				// patternTypesAndPatterns: getSettings().__experimentalBlockpatternTypesAndPatterns,
			};
		},
		[ rootClientId ]
	);

	const { createSuccessNotice } = useDispatch( noticesStore );
	const onClickPattern = useCallback( ( pattern, blocks ) => {
		onInsert(
			map( blocks, ( block ) => cloneBlock( block ) ),
			pattern.name
		);
		createSuccessNotice(
			sprintf(
				// translators: placeholder: block pattern title.
				__( 'Block pattern "%s" inserted.', 'ea-styles-library' ),
				pattern.title
			),
			{
				type: 'snackbar',
			}
		);
	}, [] );

	return [
		patterns,
		patternCategories,
		patternTypesAndPatterns,
		onClickPattern,
	];
};

export default usePatternsState;
