import { GraphQLClient } from "graphql-request";

// Cliente liviano al endpoint de WPGraphQL del WordPress headless local
// (ver cms/docker-compose.yml). Plan §2: graphql-request en vez de Apollo.
export const wpClient = new GraphQLClient(
  process.env.WPGRAPHQL_ENDPOINT ?? "http://localhost:8090/graphql"
);
