import { Search, X } from "lucide-react";
import { Input } from "@/components/ui/input";
import { cn } from "@/lib/utils";

type SearchInputProps = {
    value: string;
    onChange: (value: string) => void;
    placeholder?: string;
    className?: string;
};

export function SearchInput({
    value,
    onChange,
    placeholder = "Search",
    className,
}: SearchInputProps) {
    return (
        <div className={cn("relative", className)}>
            <Search className="text-muted-foreground pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2" />
            <Input
                type="search"
                value={value}
                onChange={(event) => onChange(event.target.value)}
                placeholder={placeholder}
                aria-label={placeholder}
                className="rounded-full pr-9 pl-9 [&::-webkit-search-cancel-button]:hidden"
            />
            {value !== "" && (
                <button
                    type="button"
                    onClick={() => onChange("")}
                    aria-label="Clear search"
                    className="text-muted-foreground absolute top-1/2 right-3 -translate-y-1/2"
                >
                    <X className="size-4" />
                </button>
            )}
        </div>
    );
}
