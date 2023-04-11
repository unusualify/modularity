export declare type MergeOptionType = 'replaceArrays' | 'concatArrays';
export declare function merge<I, F>(into: Partial<I>, from: Partial<F>, mergeOption: MergeOptionType): I & F & {};
