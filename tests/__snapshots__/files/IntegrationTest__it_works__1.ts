namespace Spatie.TypescriptTransformer.Tests.FakeClasses.Integration {
export type Dto = {
    string : string;
    nullbable : string | null;
    default : string;
    int : number;
    boolean : boolean;
    float : number;
    object : object;
    array : Array<never>;
    none : never;
    documented_string : string;
    mixed : number | string;
    documented_array : Array<number>;
    mixed_with_array : number | string | Array<number | string>;
    array_with_null : Array<number | null> | null;
    enum : Spatie.TypescriptTransformer.Tests.FakeClasses.Integration.Enum;
    non_typescripted_type : any;
    other_dto : Spatie.TypescriptTransformer.Tests.FakeClasses.Integration.OtherDto;
    other_dto_array : Array<Spatie.TypescriptTransformer.Tests.FakeClasses.Integration.OtherDto>;
    other_dto_collection : Array<Spatie.TypescriptTransformer.Tests.FakeClasses.Integration.OtherDto>;
    dto_with_children : Spatie.TypescriptTransformer.Tests.FakeClasses.Integration.DtoWithChildren;
    another_namespace_dto : Spatie.TypescriptTransformer.Tests.FakeClasses.Integration.LevelUp.YetAnotherDto;
}

export type Enum = 'yes' | 'no';
export type OtherDto = {
    name : string;
}

export type DtoWithChildren = {
    name : string;
    other_dto : Spatie.TypescriptTransformer.Tests.FakeClasses.Integration.OtherDto;
    other_dto_array : Array<Spatie.TypescriptTransformer.Tests.FakeClasses.Integration.OtherDto>;
}

}
namespace Spatie.TypescriptTransformer.Tests.FakeClasses.Integration.LevelUp {
export type YetAnotherDto = {
    name : string;
}

}
