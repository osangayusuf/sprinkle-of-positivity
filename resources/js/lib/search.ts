/**
 * Case-insensitive match of a search query against any of the given fields.
 * An empty query matches everything.
 */
export function matchesSearch(
    query: string,
    ...fields: (string | number | null | undefined)[]
): boolean {
    const needle = query.trim().toLowerCase();

    if (needle === "") {
        return true;
    }

    return fields.some((field) =>
        String(field ?? "")
            .toLowerCase()
            .includes(needle),
    );
}
