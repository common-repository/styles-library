/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import PatternExplorerSidebar from './sidebar';
import PatternExplorerPreview from './preview';
import { useEffect } from '@wordpress/element';

/**
 * Render the block pattern inserter.
 *
 * @since 0.1.0
 * @param {Object} props All the props passed to this function
 * @return {string}      Return the rendered JSX
 */
export default function PatternExplorer( props ) {
	const {
		apiResponse,
		allPatterns,
		initialCategory,
		patternCategories,
		patternType,
		setPatternType
	} = props;

	const [ selectedCategory, setSelectedCategory ] = useState(
		initialCategory?.name
	);

	const [ searchValue, setSearchValue ] = useState( '' );

	useEffect( () => {
		setSelectedCategory( initialCategory?.name );
	}, [ patternType, initialCategory, setSelectedCategory ] );

	return (
		<div className="block-pattern-explorer">
			<PatternExplorerSidebar
				patternCategories={ patternCategories }
				selectedCategory={ selectedCategory }
				setSelectedCategory={ setSelectedCategory }
				searchValue={ searchValue }
				setSearchValue={ setSearchValue }
			/>

			<PatternExplorerPreview
				apiResponse={ apiResponse }
				allPatterns={ allPatterns }
				patternCategories={ patternCategories }
				selectedCategory={ selectedCategory }
				searchValue={ searchValue }
				patternType={ patternType }
				setPatternType={ setPatternType }
			/>
		</div>
	);
}
