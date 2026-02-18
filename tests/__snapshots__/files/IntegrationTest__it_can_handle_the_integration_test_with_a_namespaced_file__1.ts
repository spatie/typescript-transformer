declare namespace Spatie {
namespace TypeScriptTransformer {
namespace Tests {
namespace Fakes {
namespace Integration {
export type Enum = "yes" | "no";
export type IntegrationClass = {
string: string
nullable: string | null
default: string
int: number
boolean: boolean
float: number
object: object
array: Array<any>
mixed: any
none: unknown
var_annotated: string
union: number | string
annotated_array: number[]
complex_annotated_array: {
int: number
string: string
level_up: Spatie.TypeScriptTransformer.Tests.Fakes.Integration.Level.LevelUpClass
}
complex_union: number | string | (number| string)[]
enum: Spatie.TypeScriptTransformer.Tests.Fakes.Integration.Enum
non_typescript_type: undefined
array_of_reference: Spatie.TypeScriptTransformer.Tests.Fakes.Integration.IntegrationItem[]
replacement_type: string
annotated_replacement_type: string
array_annotated_replacement_type: string[]
level_up_class: Spatie.TypeScriptTransformer.Tests.Fakes.Integration.Level.LevelUpClass
readonly readonly: string
optional?: string
constructor_annotated_array: number[]
constructor_inline_annotated_array: Spatie.TypeScriptTransformer.Tests.Fakes.Integration.Level.LevelUpClass[]
};
export type IntegrationItem = {
name: string
};
namespace Level {
export type LevelUpClass = {
name: string
};
}
}
}
}
}
}
