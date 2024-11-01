/**
 * External dependencies.
 */
import { parse } from '@wordpress/blocks';
/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';
import { render, useState, useMemo, useCallback, useEffect } from '@wordpress/element';
import { dispatch, subscribe } from '@wordpress/data';
import { Button, Modal } from '@wordpress/components';
import { layout } from '@wordpress/icons';
import { ReactComponent as toggleButtonIcon } from '../../../../assets/images/block-patterns.svg';

/**
 * Internal dependencies
 */
import PatternExplorer from './pattern-explorer';
import usePatternsState from './core-hooks/use-patterns-state';

/**
 * Render the header toolbar button and the accompanying pattern explorer modal.
 *
 * @since 0.1.0
 * @return {string} Return the rendered JSX for the Pattern Explorer Button
 */
function HeaderToolbarButton() {
	const [ isModalOpen, setIsModalOpen ] = useState( false );
	let [ allPatterns, allCategories, patternTypesAndPatterns ] = usePatternsState();
	const [ patternType, setPatternType ] = useState( 'default' );

	if ( patternTypesAndPatterns && patternType && 'default' !== patternType ) {

		allCategories = patternTypesAndPatterns[patternType].categories ? patternTypesAndPatterns[patternType].categories : [];
		const selectPatterns = patternTypesAndPatterns[patternType].patterns ? patternTypesAndPatterns[patternType].patterns : [];

		const parsedPatterns = selectPatterns.map( ( pattern ) => {
			return {
				...pattern,
				blocks: parse( pattern.content, {
					__unstableSkipMigrationLogs: true,
				} ),
			};
		});

		allPatterns = parsedPatterns;
	}

	// Check if a pattern has an assigned pattern category.
	const hasRegisteredCategory = useCallback(
		( pattern ) => {
			if ( ! pattern.categories || ! pattern.categories.length ) {
				return false;
			}

			return pattern.categories.some( ( cat ) =>
				allCategories.some( ( category ) => category.name === cat )
			);
		},
		[ allCategories ]
	);

	// Remove any categories without patterns.
	const populatedCategories = useMemo( () => {
		const categories = allCategories
			.filter( ( category ) =>
				allPatterns.some( ( pattern ) =>
					pattern.categories?.includes( category.name )
				)
			)
			.sort( ( { name: currentName }, { name: nextName } ) => {
				if ( ! [ currentName, nextName ].includes( 'featured' ) ) {
					return 0;
				}
				return currentName === 'featured' ? -1 : 1;
			} );

		// If there are patterns without categories, create Uncategorized.
		if (
			allPatterns.some(
				( pattern ) => ! hasRegisteredCategory( pattern )
			) &&
			! categories.find(
				( category ) => category.name === 'uncategorized'
			)
		) {
			categories.push( {
				name: 'uncategorized',
				label: __( 'Uncategorized', 'ea-styles-library' ),
			} );
		}

		return categories;
	}, [ allPatterns, allCategories ] );

	// the first category type.
	const initialCategory = populatedCategories[ 0 ] ? populatedCategories[ 0 ] : 'uncategorized';

	return (
		<>
			<Button
				icon={ toggleButtonIcon }
				className="easl-pattern-explorer-btn"
				label={ __( 'Styles Library', 'ea-styles-library' ) }
				aria-pressed= { isModalOpen }
				onClick={ () => setIsModalOpen( true ) }
			>
			</Button>
			{ isModalOpen && (
				<Modal
					title={ __( 'Styles Library', 'ea-styles-library' ) }
					closeLabel={ __( 'Close', 'ea-styles-library' ) }
					onRequestClose={ () => setIsModalOpen( false ) }
					className="block-pattern-explorer__modal"
					isFullScreen
					>
					<PatternExplorer
						apiResponse = { patternTypesAndPatterns }
						allPatterns={ allPatterns }
						initialCategory={ initialCategory }
						patternCategories={ populatedCategories }
						patternType={ patternType }
						setPatternType={ setPatternType }
					/>
				</Modal>
			) }
		</>
	);
}

/**
 * Add the header toolbar button to the block editor.
 */
subscribe( () => {
	const inserter = document.querySelector( '#ea-styles-library' );

	// If the inserter already exists, bail.
	if ( inserter ) {
		return;
	}

	wp.domReady( () => {
		let toolbar = document.querySelector( '.edit-post-header-toolbar__left' );

		// Check for FSE editor header.
		if ( ! toolbar ) {
			toolbar = document.querySelector( '.edit-site-header__toolbar' );
		}

		// If no toolbar can be found at all, bail.
		if ( ! toolbar ) {
			return;
		}

		const buttonContainer = document.createElement( 'div' );
		buttonContainer.id = 'ea-styles-library';

		toolbar.appendChild( buttonContainer );

		render(
			<HeaderToolbarButton />,
			document.getElementById( 'ea-styles-library' )
		);
	} );
} );

/**
 * Add our custom entities for retrieving external data in the Block Editor.
 *
 * @since 0.2.0
 */
dispatch( 'core' ).addEntities( [
	{
		label: __( 'Pattern And Types', 'ea-styles-library' ),
		kind: 'easl/v1',
		name: 'patternTypesAndPatterns',
		baseURL: '/easl/v1/pattern-types',
	},
] );
